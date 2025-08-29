<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDriverDetail;
use App\Models\DriverDocumentStatus;
use App\Models\DocumentCategory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;

class MigrateDriverDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:driver-documents {--driver-id= : Specific driver ID to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing driver documents to the new driver_document_status table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting driver documents migration...');

        $driverId = $this->option('driver-id');
        
        if ($driverId) {
            $drivers = UserDriverDetail::where('id', $driverId)->get();
            if ($drivers->isEmpty()) {
                $this->error("Driver with ID {$driverId} not found.");
                return 1;
            }
        } else {
            $drivers = UserDriverDetail::all();
        }

        $this->info("Found {$drivers->count()} drivers to process.");
        
        $totalMigrated = 0;
        $progressBar = $this->output->createProgressBar($drivers->count());
        $progressBar->start();

        foreach ($drivers as $driver) {
            $migrated = $this->migrateDriverDocuments($driver);
            $totalMigrated += $migrated;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Migration completed! Total documents migrated: {$totalMigrated}");
        
        return 0;
    }

    /**
     * Migrate documents for a specific driver
     */
    private function migrateDriverDocuments(UserDriverDetail $driver): int
    {
        $migrated = 0;
        
        try {
            DB::beginTransaction();

            // Get all media files associated with this driver
            $mediaFiles = Media::where('model_type', UserDriverDetail::class)
                ->where('model_id', $driver->id)
                ->get();

            foreach ($mediaFiles as $media) {
                // Skip if already migrated
                if (DriverDocumentStatus::where('media_id', $media->id)->exists()) {
                    continue;
                }

                $category = $this->determineCategoryFromMedia($media);
                $status = $this->determineStatusFromMedia($media, $driver);
                $expiryDate = $this->determineExpiryDate($media, $category);

                DriverDocumentStatus::create([
                    'driver_id' => $driver->id,
                    'media_id' => $media->id,
                    'category' => $category,
                    'status' => $status,
                    'expiry_date' => $expiryDate,
                    'notes' => null
                ]);

                $migrated++;
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error migrating documents for driver {$driver->id}: " . $e->getMessage());
        }

        return $migrated;
    }

    /**
     * Determine document category from media collection
     */
    private function determineCategoryFromMedia(Media $media): string
    {
        $collection = $media->collection_name;
        
        // Map collection names to categories
        $categoryMap = [
            'license_front' => 'license',
            'license_back' => 'license',
            'medical_card' => 'medical',
            'medical_certificate' => 'medical',
            'driving_record' => 'record',
            'criminal_record' => 'record',
            'clearing_house' => 'record',
            'training_certificate' => 'training',
            'course_certificate' => 'training',
            'inspection_report' => 'inspection',
            'accident_report' => 'accident',
            'employment_verification' => 'employment',
            'default' => 'other'
        ];

        return $categoryMap[$collection] ?? $categoryMap['default'];
    }

    /**
     * Determine document status
     */
    private function determineStatusFromMedia(Media $media, UserDriverDetail $driver): string
    {
        // Check if document has expired based on driver data
        if ($this->isDocumentExpired($media, $driver)) {
            return 'expired';
        }

        // Default to active for existing documents
        return 'active';
    }

    /**
     * Determine expiry date based on document type and driver data
     */
    private function determineExpiryDate(Media $media, string $category): ?\Carbon\Carbon
    {
        $collection = $media->collection_name;
        
        // For license documents, we might have expiry info in driver details
        if (in_array($collection, ['license_front', 'license_back'])) {
            // You might want to extract this from driver license expiry date
            // return $driver->license_expiry_date;
        }
        
        // For medical documents
        if (in_array($collection, ['medical_card', 'medical_certificate'])) {
            // You might want to extract this from driver medical expiry date
            // return $driver->medical_expiry_date;
        }

        return null;
    }

    /**
     * Check if document is expired
     */
    private function isDocumentExpired(Media $media, UserDriverDetail $driver): bool
    {
        // Add logic to check if document is expired based on your business rules
        // This is a placeholder - implement based on your specific requirements
        return false;
    }
}
