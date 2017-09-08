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
