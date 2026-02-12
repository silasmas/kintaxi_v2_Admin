<?php

namespace App\Providers;

use App\Services\MediaStorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaStorageService::class, function () {
            $disk = env('FILAMENT_FILESYSTEM_DISK', 's3_media');
            return new MediaStorageService($disk);
        });
    }

    public function boot(): void
    {
        //
    }
}
