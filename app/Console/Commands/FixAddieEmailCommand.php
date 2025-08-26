<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FixAddieEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-addie {--new-email=} {--delete : Delete the user instead of updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the addie.bailey@example.com user by updating email or deleting the user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find the user with addie.bailey@example.com
        $user = User::where('email', 'addie.bailey@example.com')->first();

        if (!$user) {
            $this->info('No user found with email addie.bailey@example.com');
            return;
        }

        $this->info('Found user:');
        $this->line("ID: {$user->id}");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Roles: " . $user->roles->pluck('name')->join(', '));
        $this->newLine();

        if ($this->option('delete')) {
            if ($this->confirm('Are you sure you want to DELETE this user? This action cannot be undone.')) {
                $userName = $user->name;
                $userEmail = $user->email;
                
                $user->delete();
                
                $this->info("User '{$userName}' with email '{$userEmail}' has been deleted successfully.");
                
                Log::info('User with addie.bailey@example.com deleted', [
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $userEmail
                ]);
            } else {
                $this->info('Operation cancelled.');
            }
            return;
        }

        $newEmail = $this->option('new-email');
        
        if (!$newEmail) {
            $newEmail = $this->ask('Enter the new email address for this user');
        }

        if (!$newEmail) {
            $this->error('No email provided. Operation cancelled.');
            return;
        }

        // Validate email format
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format provided.');
            return;
        }

        // Check if email already exists
        $existingUser = User::where('email', $newEmail)->where('id', '!=', $user->id)->first();
        if ($existingUser) {
            $this->error("Email '{$newEmail}' is already in use by another user (ID: {$existingUser->id}).");
            return;
        }

        if ($this->confirm("Update user email from '{$user->email}' to '{$newEmail}'?")) {
            $oldEmail = $user->email;
            $user->email = $newEmail;
            $user->save();

            $this->info("User email updated successfully from '{$oldEmail}' to '{$newEmail}'.");
            
            Log::info('User email updated', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'old_email' => $oldEmail,
                'new_email' => $newEmail
            ]);
        } else {
            $this->info('Operation cancelled.');
        }
    }
}