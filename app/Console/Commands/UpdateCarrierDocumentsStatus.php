<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Carrier;

class UpdateCarrierDocumentsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:update-documents-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de documents_completed para todos los carriers existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando estado de documentos para todos los carriers...');
        
        $carriers = Carrier::all();
        $updated = 0;
        
        foreach ($carriers as $carrier) {
            $oldStatus = $carrier->documents_completed;
            $carrier->checkDocumentsCompletion();
            
            if ($oldStatus !== $carrier->fresh()->documents_completed) {
                $updated++;
                $this->line("Carrier {$carrier->name}: documents_completed actualizado a " . 
                          ($carrier->fresh()->documents_completed ? 'true' : 'false'));
            }
        }
        
        $this->info("Proceso completado. {$updated} carriers actualizados de {$carriers->count()} totales.");
        
        return Command::SUCCESS;
    }
}
