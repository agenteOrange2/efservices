<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email=frontend@kuiraweb.com : Email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un correo de prueba para verificar la configuración SMTP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Enviando correo de prueba a: {$email}");
        
        try {
            Mail::raw('Este es un correo de prueba enviado desde Laravel para verificar que la configuración SMTP está funcionando correctamente.\n\nFecha y hora: ' . now()->format('Y-m-d H:i:s') . '\n\nSi recibes este correo, significa que el sistema de correos está funcionando correctamente.', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Prueba de correo desde Laravel - ' . config('app.name'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Correo enviado exitosamente!');
            $this->info('📧 Destinatario: ' . $email);
            $this->info('📤 Remitente: ' . config('mail.from.address'));
            $this->info('🔧 Servidor SMTP: ' . config('mail.mailers.smtp.host'));
            
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar el correo:');
            $this->error($e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
