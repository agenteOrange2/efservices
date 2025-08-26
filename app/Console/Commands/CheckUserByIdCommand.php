<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserByIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-id {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check a specific user by ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('id');
        
        $user = User::with('roles')->find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }
        
        $this->info("User ID {$userId} found:");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Roles: " . $user->roles->pluck('name')->join(', '));
        $this->line("Created: {$user->created_at}");
        $this->line("Updated: {$user->updated_at}");
    }
}