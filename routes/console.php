<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('temp:clean {--hours=24}', function (TempUploadService $tempUploadService) {
    $hours = (int) $this->option('hours');
    $count = $tempUploadService->cleanOldFiles($hours);
    
    $this->info("{$count} temporary files older than {$hours} hours have been cleaned up.");
})->purpose('Clean old temporary files');

// Programar la limpieza diaria
Schedule::command('temp:clean')->daily();