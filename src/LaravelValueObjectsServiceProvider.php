<?php

namespace MichaelRavedoni\LaravelValueObjects;

use Illuminate\Support\ServiceProvider;
use MichaelRavedoni\LaravelValueObjects\Commands\MakeValueObjectCommand;

class LaravelValueObjectsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Pas de registrations complexes nécessaires ici pour l'instant
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootCommands();
    }

    /**
     * Bootstrap the package's Artisan commands.
     *
     * @return void
     */
    protected function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeValueObjectCommand::class,
            ]);
        }
    }
}