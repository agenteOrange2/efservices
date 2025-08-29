<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\CarrierStepCompleted;
use App\Events\CarrierRegistrationCompleted;
use App\Models\User;
use App\Models\Carrier;
use App\Models\NotificationSetting;
use App\Models\NotificationLog;

class TestNotificationSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-notification-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system by firing events and checking logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Notification System...');
        
        // Verificar que las tablas existen
        $this->info('Checking database tables...');
        
        try {
            $settingsCount = NotificationSetting::count();
            $logsCount = NotificationLog::count();
            $this->info("Notification Settings: {$settingsCount} records");
            $this->info("Notification Logs: {$logsCount} records");
        } catch (\Exception $e) {
            $this->error('Database tables not found: ' . $e->getMessage());
            return 1;
        }
        
        // Buscar un usuario de prueba
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database');
            return 1;
        }
        
        $this->info("Using test user: {$user->email}");
        
        // Buscar un carrier de prueba
        $carrier = Carrier::first();
        if (!$carrier) {
            $this->info('No carriers found, creating test data...');
            $carrier = new Carrier();
            $carrier->company_name = 'Test Carrier Company';
            $carrier->dot_number = 'TEST123';
            $carrier->mc_number = 'MC123';
            $carrier->ein = '12-3456789';
            $carrier->save();
        }
        
        $this->info("Using test carrier: {$carrier->company_name}");
        
        // Disparar evento CarrierStepCompleted
        $this->info('Firing CarrierStepCompleted event...');
        event(new CarrierStepCompleted($user, 'step1', [
            'carrier_id' => $carrier->id,
            'data' => ['test' => true]
        ]));
        
        // Disparar evento CarrierRegistrationCompleted
        $this->info('Firing CarrierRegistrationCompleted event...');
        event(new CarrierRegistrationCompleted($user, $carrier, [
            'registration_method' => 'wizard',
            'total_steps' => 4,
            'documents_generated' => true,
            'membership_id' => 1
        ]));
        
        // Esperar un momento para que se procesen los eventos
        sleep(2);
        
        // Verificar logs
        $this->info('Checking notification logs...');
        $recentLogs = NotificationLog::where('created_at', '>=', now()->subMinutes(1))->get();
        
        if ($recentLogs->count() > 0) {
            $this->info("Found {$recentLogs->count()} recent notification logs:");
            foreach ($recentLogs as $log) {
                $this->line("- Event: {$log->event_type}, Status: {$log->status}, Channel: {$log->channel}");
            }
        } else {
            $this->warn('No recent notification logs found');
        }
        
        $this->info('Test completed!');
        return 0;
    }
}
