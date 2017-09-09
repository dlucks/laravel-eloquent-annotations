<?php

namespace EloquentAnnotations\Providers;

use EloquentAnnotations\Console\Commands\AnnotateModels;
use MakeUser\Console\Commands\MakeUser;
use Illuminate\Support\ServiceProvider;

class EloquentAnnotationsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register command.
        if ($this->app->runningInConsole()) {
            $this->commands([
                AnnotateModels::class,
            ]);
        }

        // Publish vendor files (config, translations).
        $this->publishes([
            __DIR__ . '/../config/eloquent_annotations.php' => config_path('eloquent_annotations.php'),
        ], 'eloquent_annotations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
