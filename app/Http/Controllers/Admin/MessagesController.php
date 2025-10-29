<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\MessageRecipient;
use App\Models\MessageStatusLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AdminMessage::with(['sender', 'recipients'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->paginate(15)->withQueryString();

        // Get statistics for dashboard
        $stats = [
            'total' => AdminMessage::count(),
            'sent' => AdminMessage::byStatus('sent')->count(),
            'delivered' => AdminMessage::byStatus('delivered')->count(),
            'failed' => AdminMessage::byStatus('failed')->count(),
            'today' => AdminMessage::whereDate('created_at', today())->count(),
        ];

        return view('admin.messages.index', compact('messages', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all drivers with their user and carrier relationships
        $drivers = \App\Models\UserDriverDetail::with(['user', 'carrier'])
            ->where('application_completed', 1)
            ->whereHas('user', function($query) {
                $query->where('status', 1);
            })
            ->get();

        // Get all carriers
        $carriers = \App\Models\Carrier::where('status', 1)
            ->orderBy('name')
            ->get();

        return view('admin.messages.create', compact('drivers', 'carriers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => ['required', Rule::in(['all_drivers', 'specific_drivers', 'custom_emails'])],
            'driver_ids' => 'required_if:recipient_type,specific_drivers|array',
            'driver_ids.*' => 'exists:user_driver_details,id',
            'custom_emails' => 'required_if:recipient_type,custom_emails|string',
            'carrier_filter' => 'nullable|exists:carriers,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'status' => ['required', Rule::in(['draft', 'sent'])],
        ]);

        DB::beginTransaction();
        
        try {
            // Create the message
            $message = AdminMessage::create([
                'sender_id' => Auth::id(),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'priority' => $validated['priority'],
                'status' => $validated['status'],
                'sent_at' => $validated['status'] === 'sent' ? now() : null
            ]);

            $recipients = [];

            // Process recipients based on type
            switch ($validated['recipient_type']) {
                case 'all_drivers':
                    $driversQuery = \App\Models\UserDriverDetail::with(['user', 'carrier'])
                        ->where('application_completed', 1)
                        ->whereHas('user', function($query) {
                            $query->where('status', 1);
                        });
                    
                    // Apply carrier filter if specified
                    if (!empty($validated['carrier_filter'])) {
                        $driversQuery->where('carrier_id', $validated['carrier_filter']);
                    }
                    
                    $drivers = $driversQuery->get();
                    
                    foreach ($drivers as $driver) {
                        $recipients[] = [
                            'message_id' => $message->id,
                            'recipient_type' => 'driver',
                            'recipient_id' => $driver->id,
                            'email' => $driver->user->email,
                            'name' => $driver->user->name,
                            'delivery_status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    break;

                case 'specific_drivers':
                    $drivers = \App\Models\UserDriverDetail::with(['user', 'carrier'])
                        ->whereIn('id', $validated['driver_ids'])
                        ->get();
                    
                    foreach ($drivers as $driver) {
                        $recipients[] = [
                            'message_id' => $message->id,
                            'recipient_type' => 'driver',
                            'recipient_id' => $driver->id,
                            'email' => $driver->user->email,
                            'name' => $driver->user->name,
                            'delivery_status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    break;

                case 'custom_emails':
                    $emails = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $validated['custom_emails'])));
                    
                    foreach ($emails as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $recipients[] = [
                                'message_id' => $message->id,
                                'recipient_type' => 'email',
                                'recipient_id' => null,
                                'email' => $email,
                                'name' => $email,
                                'delivery_status' => 'pending',
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }
                    break;
            }

            // Insert all recipients
            if (!empty($recipients)) {
                MessageRecipient::insert($recipients);
            }

            // Send emails if status is 'sent'
            if ($validated['status'] === 'sent') {
                $messageRecipients = MessageRecipient::where('message_id', $message->id)->get();
                
                foreach ($messageRecipients as $recipient) {
                    try {
                        $this->sendMessageEmail($message, $recipient);
                    } catch (\Exception $e) {
                        // Log individual email failures but continue with others
                        \Log::error('Failed to send email to ' . $recipient->email . ': ' . $e->getMessage());
                    }
                }

                MessageStatusLog::createLog($message->id, 'sent', 'Message sent to ' . count($recipients) . ' recipients');
            }

            DB::commit();

            $successMessage = $validated['status'] === 'sent' 
                ? 'Message sent successfully to ' . count($recipients) . ' recipients!'
                : 'Message saved as draft with ' . count($recipients) . ' recipients!';

            return redirect()->route('admin.messages.show', $message)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withInput()
                ->with('error', 'Failed to process message: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AdminMessage $message)
    {
        $message->load(['sender', 'recipients', 'statusLogs' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminMessage $message)
    {
        // Only allow editing of draft messages
        if ($message->status !== 'draft') {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'Only draft messages can be edited.');
        }

        // Get available drivers for adding to the message
        $availableDrivers = \App\Models\UserDriverDetail::with(['user', 'carrier'])
            ->where('application_completed', 1)
            ->whereHas('user', function($query) {
                $query->where('status', 1);
            })
            ->get();

        // Get all active carriers for filtering
        $carriers = \App\Models\Carrier::where('status', 1)->get();

        return view('admin.messages.edit', compact('message', 'availableDrivers', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdminMessage $message)
    {
        // Only allow updating of draft messages
        if ($message->status !== 'draft') {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'Only draft messages can be updated.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])]
        ]);

        $message->update($validated);

        MessageStatusLog::createLog($message->id, 'updated', 'Message content updated');

        return redirect()->route('admin.messages.show', $message)
            ->with('success', 'Message updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminMessage $message)
    {
        // Only allow deletion of draft messages
        if ($message->status !== 'draft') {
            return back()->with('error', 'Only draft messages can be deleted.');
        }

        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message deleted successfully!');
    }

    /**
     * Remove a recipient from a message
     */
    public function removeRecipient(AdminMessage $message, MessageRecipient $recipient)
    {
        // Only allow removing recipients from draft messages
        if ($message->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only recipients from draft messages can be removed.'
            ], 400);
        }

        // Verify the recipient belongs to this message
        if ($recipient->message_id !== $message->id) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient does not belong to this message.'
            ], 400);
        }

        $recipient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recipient removed successfully!'
        ]);
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        // Get status and priority distributions
        $statusDistribution = AdminMessage::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        $priorityDistribution = AdminMessage::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $stats = [
            'total' => AdminMessage::count(),
            'sent' => $statusDistribution['sent'] ?? 0,
            'draft' => $statusDistribution['draft'] ?? 0,
            'failed' => $statusDistribution['failed'] ?? 0,
            'sent_today' => AdminMessage::whereDate('created_at', today())->count(),
            'sent_this_week' => AdminMessage::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'sent_this_month' => AdminMessage::whereMonth('created_at', now()->month)->count(),
            'by_status' => $statusDistribution,
            'by_priority' => $priorityDistribution,
        ];

        // Calculate delivery statistics
        $deliveryStats = [
            'total' => MessageRecipient::count(),
            'delivered' => MessageRecipient::where('delivery_status', 'delivered')->count(),
            'pending' => MessageRecipient::where('delivery_status', 'pending')->count(),
            'failed' => MessageRecipient::where('delivery_status', 'failed')->count(),
            'read' => MessageRecipient::whereNotNull('read_at')->count(),
        ];

        $recentMessages = AdminMessage::with(['sender', 'recipients'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.messages.dashboard', compact('stats', 'recentMessages', 'statusDistribution', 'priorityDistribution', 'deliveryStats'));
    }

    /**
     * Get recipient information based on type and ID
     */
    private function getRecipientInfo($type, $id)
    {
        switch ($type) {
            case 'user':
                $user = User::find($id);
                return $user ? ['email' => $user->email, 'name' => $user->name] : null;
                
            case 'driver':
                // Assuming you have a Driver model
                $driver = \App\Models\Driver::find($id);
                return $driver ? ['email' => $driver->email, 'name' => $driver->name] : null;
                
            case 'carrier':
                // Assuming you have a Carrier model
                $carrier = \App\Models\Carrier::find($id);
                return $carrier ? ['email' => $carrier->email, 'name' => $carrier->name] : null;
                
            default:
                return null;
        }
    }

    /**
     * Resend a message to all its recipients
     */
    public function resend(AdminMessage $message)
    {
        // Only allow resending of sent messages
        if ($message->status !== 'sent') {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'Only sent messages can be resent.');
        }

        DB::beginTransaction();
        
        try {
            $recipients = MessageRecipient::where('message_id', $message->id)->get();
            $successCount = 0;
            $failureCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    // Reset recipient status before resending
                    $recipient->update([
                        'delivery_status' => 'pending',
                        'delivered_at' => null,
                        'read_at' => null
                    ]);

                    $this->sendMessageEmail($message, $recipient);
                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    \Log::error('Failed to resend email to ' . $recipient->email . ': ' . $e->getMessage());
                }
            }

            // Create status log
            $logMessage = "Message resent: {$successCount} successful, {$failureCount} failed";
            MessageStatusLog::createLog($message->id, 'resent', $logMessage);

            DB::commit();

            if ($successCount > 0) {
                $successMessage = $failureCount > 0 
                    ? "Message resent successfully to {$successCount} recipients. {$failureCount} failed."
                    : "Message resent successfully to all {$successCount} recipients!";
                
                return redirect()->route('admin.messages.show', $message)
                    ->with('success', $successMessage);
            } else {
                return redirect()->route('admin.messages.show', $message)
                    ->with('error', 'Failed to resend message to any recipients.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'Failed to resend message: ' . $e->getMessage());
        }
    }

    /**
     * Send message via email
     */
    private function sendMessageEmail(AdminMessage $message, MessageRecipient $recipient)
    {
        try {
            $messageSubject = $message->subject;
            
            Mail::send('emails.admin-message', [
                'adminMessage' => $message,
                'recipient' => $recipient
            ], function ($mail) use ($messageSubject, $recipient) {
                $mail->to($recipient->email, $recipient->name)
                     ->subject($messageSubject);
            });

            $recipient->update([
                'delivery_status' => 'sent',
                'delivered_at' => now()
            ]);

        } catch (\Exception $e) {
            $recipient->update(['delivery_status' => 'failed']);
            MessageStatusLog::createLog($message->id, 'failed', 'Email delivery failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
