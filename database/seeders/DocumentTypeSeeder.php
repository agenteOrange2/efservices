<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define los tipos de documentos
        $documentTypes = [
            ['name' => 'Politics', 'requirement' => true],
            ['name' => 'MC90', 'requirement' => true],
            ['name' => 'RANDOM CERTIFICATE', 'requirement' => true],
            ['name' => 'SUPERVISOR TRAINING', 'requirement' => true],
            ['name' => 'Certificate of Filing', 'requirement' => true],
            ['name' => 'COI', 'requirement' => true],
            ['name' => 'Hazmat Certificate', 'requirement' => false],
        ];

        // Insertar los tipos de documentos en la base de datos
        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(
                ['name' => $type['name']], // Verifica si existe el nombre del documento
                ['requirement' => $type['requirement']]
            );
        }

        $this->command->info('Document types seeded successfully.');
    }
}
