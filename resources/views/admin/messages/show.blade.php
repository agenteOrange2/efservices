@extends('../themes/' . $activeTheme)
@section('title', 'Message Details')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Messages', 'url' => route('admin.messages.index')],
['label' => 'Message Details', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="alert alert-success flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
        {{ session('error') }}
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
        <div>
            <h2 class="text-lg font-medium">Message Details</h2>
            <div class="text-slate-500 text-sm mt-1">
                View complete message information and delivery status
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                Back to Messages
            </x-base.button>
            @if($message->status === 'draft')
            <x-base.button as="a" href="{{ route('admin.messages.edit', $message) }}" variant="primary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                Edit Message
            </x-base.button>
            @endif
        </div>
    </div>

    <!-- Message Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-5">
        <!-- Main Message Content -->
        <div class="lg:col-span-2">
            <div class="box box--stacked">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <div class="flex items-center justify-between">
                        <h3 class="box-title">Message Content</h3>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->priority_color }}">
                                {{ ucfirst($message->priority) }} Priority
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->status_color }}">
                                {{ ucfirst($message->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="box-body p-5">
                    <div class="mb-4">
                        <label class="text-sm font-medium text-slate-600">Subject:</label>
                        <div class="text-lg font-medium mt-1">{{ $message->subject }}</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-sm font-medium text-slate-600">Message:</label>
                        <div class="mt-2 p-4 bg-slate-50 rounded-lg border">
                            <div class="whitespace-pre-wrap">{{ $message->message }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-slate-600">Sent By:</label>
                            <div class="font-medium">{{ $message->sender->name ?? 'System' }}</div>
                            <div class="text-slate-500">{{ $message->sender->email ?? 'system@efservices.com' }}</div>
                        </div>
                        <div>
                            <label class="text-slate-600">Sent Date:</label>
                            @if($message->sent_at)
                            <div class="font-medium">{{ $message->sent_at->format('M d, Y H:i') }}</div>
                            <div class="text-slate-500">{{ $message->sent_at->diffForHumans() }}</div>
                            @else
                            <div class="text-slate-400">Not sent yet</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients List -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Recipients ({{ $message->recipients->count() }})</h3>
                </div>
                <div class="box-body p-0">
                    @if($message->recipients->count() > 0)
                    <div class="overflow-x-auto">
                        <x-base.table>
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">Recipient</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Type</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Delivery Status</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Delivered At</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Read At</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach($message->recipients as $recipient)
                                <x-base.table.tr>
                                    <x-base.table.td>
                                        <div class="font-medium">{{ $recipient->name }}</div>
                                        <div class="text-slate-500 text-xs">{{ $recipient->email }}</div>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($recipient->recipient_type) }}
                                        </span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $recipient->delivery_status_color }}">
                                            {{ ucfirst($recipient->delivery_status) }}
                                        </span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        @if($recipient->delivered_at)
                                        <div class="text-sm">{{ $recipient->delivered_at->format('M d, Y H:i') }}</div>
                                        <div class="text-xs text-slate-500">{{ $recipient->delivered_at->diffForHumans() }}</div>
                                        @else
                                        <span class="text-slate-400">-</span>
                                        @endif
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        @if($recipient->read_at)
                                        <div class="text-sm">{{ $recipient->read_at->format('M d, Y H:i') }}</div>
                                        <div class="text-xs text-slate-500">{{ $recipient->read_at->diffForHumans() }}</div>
                                        @else
                                        <span class="text-slate-400">-</span>
                                        @endif
                                    </x-base.table.td>
                                </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    @else
                    <div class="p-10 text-center">
                        <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto mb-4" icon="users" />
                        <h3 class="text-lg font-medium text-slate-600 mb-2">No Recipients</h3>
                        <p class="text-slate-500">This message has no recipients assigned.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Stats -->
            <div class="box box--stacked">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Message Statistics</h3>
                </div>
                <div class="box-body p-5">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Total Recipients:</span>
                            <span class="font-medium">{{ $message->recipients->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Delivered:</span>
                            <span class="font-medium text-green-600">{{ $message->recipients->where('delivery_status', 'delivered')->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Pending:</span>
                            <span class="font-medium text-yellow-600">{{ $message->recipients->where('delivery_status', 'pending')->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Failed:</span>
                            <span class="font-medium text-red-600">{{ $message->recipients->where('delivery_status', 'failed')->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Read:</span>
                            <span class="font-medium text-blue-600">{{ $message->recipients->whereNotNull('read_at')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Status History</h3>
                </div>
                <div class="box-body p-5">
                    @if($message->statusLogs->count() > 0)
                    <div class="space-y-4">
                        @foreach($message->statusLogs->sortByDesc('created_at') as $log)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ ucfirst($log->status) }}</div>
                                @if($log->notes)
                                <div class="text-slate-500 text-xs mt-1">{{ $log->notes }}</div>
                                @endif
                                <div class="text-slate-400 text-xs mt-1">{{ $log->created_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-slate-500">
                        <x-base.lucide class="w-8 h-8 mx-auto mb-2" icon="clock" />
                        <div class="text-sm">No status history available</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Actions</h3>
                </div>
                <div class="box-body p-5">
                    <div class="space-y-3">
                        @if($message->status === 'draft')
                        <x-base.button as="a" href="{{ route('admin.messages.edit', $message) }}" variant="primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                            Edit Message
                        </x-base.button>
                        @endif
                        
                        <x-base.button onclick="duplicateMessage({{ $message->id }})" variant="outline-primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="copy" />
                            Duplicate Message
                        </x-base.button>
                        
                        @if($message->status !== 'draft')
                        <x-base.button onclick="resendMessage({{ $message->id }})" variant="outline-warning" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="send" />
                            Resend Message
                        </x-base.button>
                        @endif
                        
                        <x-base.button onclick="deleteMessage({{ $message->id }})" variant="outline-danger" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="trash-2" />
                            Delete Message
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<x-base.dialog id="deleteMessageModal">
    <x-base.dialog.panel>
        <div class="p-5 text-center">
            <x-base.lucide class="w-16 h-16 text-red-500 mx-auto mt-3" icon="x-circle" />
            <div class="text-3xl mt-5">Are you sure?</div>
            <div class="text-slate-500 mt-2">
                Do you really want to delete this message? This process cannot be undone.
            </div>
        </div>
        <div class="px-5 pb-8 text-center">
            <x-base.button type="button" variant="outline-secondary" class="w-24 mr-1" data-tw-dismiss="modal">
                Cancel
            </x-base.button>
            <x-base.button type="button" variant="danger" class="w-24" onclick="confirmDelete()">
                Delete
            </x-base.button>
        </div>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
let messageToDelete = null;

function deleteMessage(messageId) {
    messageToDelete = messageId;
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#deleteMessageModal"));
    modal.show();
}

function confirmDelete() {
    if (messageToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/messages/${messageToDelete}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function duplicateMessage(messageId) {
    window.location.href = `/admin/messages/create?duplicate=${messageId}`;
}

function resendMessage(messageId) {
    if (confirm('Are you sure you want to resend this message to all recipients?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/messages/${messageId}/resend`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection