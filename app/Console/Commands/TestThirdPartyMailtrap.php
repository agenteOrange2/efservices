<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\ThirdPartyVehicleVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestThirdPartyMailtrap extends Command
{
    protected $signature = 'mail:test-third-party {email}';
    protected $description = 'Enviar correo de prueba usando ThirdPartyVehicleVerification con Mailtrap';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Enviando correo de verificación de tercero a: {$email}");
        
        try {
            // Datos de prueba similares a los que usa la aplicación
            $token = 'test-token-' . uniqid();
            $driverName = 'Test Driver';
            $thirdPartyName = 'Test Third Party';
            $vehicleInfo = [
                'year' => '2020',
                'make' => 'Toyota',
                'model' => 'Camry',
                'vin' => 'TEST123456789'
            ];
            
            // Log de configuración SMTP actual
            Log::info('Configuración SMTP para test third party', [
                'mail_mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_from_address' => config('mail.from.address'),
                'recipient_email' => $email
            ]);
            
            Mail::to($email)->send(new ThirdPartyVehicleVerification(
                $token,
                $driverName,
                $thirdPartyName,
                $vehicleInfo
            ));
            
            $this->info('✅ Correo de verificación de tercero enviado exitosamente!');
            $this->info("📧 Destinatario: {$email}");
            $this->info('📤 Remitente: ' . config('mail.from.address'));
            $this->info('🔧 Servidor SMTP: ' . config('mail.mailers.smtp.host'));
            $this->info("🔑 Token: {$token}");
            
            Log::info('Correo de verificación de tercero enviado exitosamente', [
                'recipient' => $email,
                'token' => $token
            ]);
            
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar correo: ' . $e->getMessage());
            Log::error('Error enviando correo de verificación de tercero', [
                'recipient' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}