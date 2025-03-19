<?php

namespace App\Notifications\Admin\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminNewUserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;
    
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
        Log::info('AdminNewUserCreatedNotification constructor called', [
            'new_user_id' => $newUser->id,
            'new_user_email' => $newUser->email
        ]);
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('AdminNewUserCreatedNotification toMail method called', [
            'admin_email' => config('app.admin_email'),
            'new_user_details' => [
                'name' => $this->newUser->name,
                'email' => $this->newUser->email
            ]
        ]);
        

        return (new MailMessage)
        ->subject('New Administrator User Created')
        ->greeting('Hello Administrator!')
        ->line('A new administrator user has been created in the system.')
        ->line('New user details:')
        ->line('Name: ' . $this->newUser->name)
        ->line('Email: ' . $this->newUser->email)
        ->line('Creation date: ' . $this->newUser->created_at->format('d/m/Y H:i'))
        ->action('View User', route('admin.users.edit', $this->newUser->id));
    }

    public function toDatabase($notifiable)
    {
        return [
            'user_id' => $this->newUser->id,
            'title' => 'New user created',
            'message' => "New user registered: {$this->newUser->name}",
            'icon' => 'UserPlus', // Icono para usar en la UI                        
            'action_url' => '/admin/users/' . $this->newUser->id
        ];
    }
}
