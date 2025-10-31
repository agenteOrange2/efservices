<?php

namespace App\Console\Commands;

use App\Models\Carrier;
use Illuminate\Console\Command;

class ListCarriers extends Command
{
    protected $signature = 'carriers:list';
    protected $description = 'List all carriers in the database';

    public function handle()
    {
        $carriers = Carrier::select('id', 'name', 'slug', 'status')->get();
        
        if ($carriers->isEmpty()) {
            $this->info('No carriers found in the database.');
            return;
        }

        $this->info('Carriers in database:');
        $this->line('');
        
        foreach ($carriers as $carrier) {
            $this->line("ID: {$carrier->id} | Name: {$carrier->name} | Slug: {$carrier->slug} | Status: {$carrier->status}");
        }
        
        $this->line('');
        $this->info("Total carriers: {$carriers->count()}");
    }
}