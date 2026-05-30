<?php

namespace Abilara;

use Illuminate\Support\ServiceProvider;
use Abilara\Console\AbilaraInstallCommand;

class AbilaraServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AbilaraInstallCommand::class,
            ]);
        }
    }
}
