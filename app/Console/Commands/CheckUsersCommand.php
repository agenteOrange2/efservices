<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check {--email=} {--example : Show only example.com emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all users in the database, especially looking for addie.bailey@example.com or example.com emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking users in the database...');
        $this->newLine();

        // Get all users
        $users = User::with('roles')->get();

        if ($users->isEmpty()) {
            $this->warn('No users found in the database.');
            return;
        }

        $this->info('Total users found: ' . $users->count());
        $this->newLine();

        // Check for specific email if provided
        if ($this->option('email')) {
            $email = $this->option('email');
            $specificUser = $users->where('email', $email)->first();
            
            if ($specificUser) {
                $this->error("FOUND USER WITH EMAIL: {$email}");
                $this->displayUser($specificUser);
            } else {
                $this->info("No user found with email: {$email}");
            }
            $this->newLine();
        }

        // Check for example.com emails
        $exampleUsers = $users->filter(function ($user) {
            return str_contains($user->email, 'example.com');
        });

        if ($exampleUsers->isNotEmpty()) {
            $this->error('USERS WITH EXAMPLE.COM EMAILS FOUND:');
            foreach ($exampleUsers as $user) {
                $this->displayUser($user);
            }
            $this->newLine();
        }

        // Show only example.com emails if requested
        if ($this->option('example')) {
            if ($exampleUsers->isEmpty()) {
                $this->info('No users with example.com emails found.');
            }
            return;
        }

        // Display all users
        $this->info('ALL USERS:');
        $headers = ['ID', 'Name', 'Email', 'Roles', 'Created At'];
        $rows = [];

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $roles ?: 'No roles',
                $user->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);

        // Specifically check for addie.bailey@example.com
        $addieUser = $users->where('email', 'addie.bailey@example.com')->first();
        if ($addieUser) {
            $this->newLine();
            $this->error('CRITICAL: Found user with addie.bailey@example.com!');
            $this->displayUser($addieUser);
        } else {
            $this->newLine();
            $this->info('No user found with addie.bailey@example.com email.');
        }

        // Log the results
        Log::info('CheckUsersCommand executed', [
            'total_users' => $users->count(),
            'example_com_users' => $exampleUsers->count(),
            'addie_bailey_found' => $addieUser ? true : false
        ]);
    }

    private function displayUser($user)
    {
        $roles = $user->roles->pluck('name')->join(', ');
        $this->line("ID: {$user->id}");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Roles: " . ($roles ?: 'No roles'));
        $this->line("Created: {$user->created_at->format('Y-m-d H:i:s')}");
        $this->line("Updated: {$user->updated_at->format('Y-m-d H:i:s')}");
        $this->newLine();
    }
}