<?php

namespace Goodechilde\Arche;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\ServiceProvider;
use Goodechilde\Arche\Console\Commands\ControllerMakeCommand;
use Goodechilde\Arche\Console\Commands\FactoryMakeCommand;
use Goodechilde\Arche\Console\Commands\Arche;
use Goodechilde\Arche\Console\Commands\MigrateMakeCommand;
use Goodechilde\Arche\Console\Commands\ModelMakeCommand;
use Goodechilde\Arche\Console\Commands\RequestMakeCommand;
use Goodechilde\Arche\Console\Commands\ViewMakeCommand;

class ArcheServiceProvider extends ServiceProvider
{

    protected $commands = [
        ControllerMakeCommand::class,
        FactoryMakeCommand::class,
        Arche::class,
        MigrateMakeCommand::class,
        ModelMakeCommand::class,
        RequestMakeCommand::class,
        ViewMakeCommand::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cray');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'cray');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

//        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('arche.php'),
            ], 'cray');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/cray'),
            ], 'views');*/

            // Publishing assets.
            $this->publishes([
                __DIR__ . '/../resources/stubs' => resource_path('stubs')
            ], 'cray');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/cray'),
            ], 'lang');*/

        $this->app->when(MigrationCreator::class)
            ->needs('$customStubPath')
            ->give(function ($app) {
                return resource_path('stubs');
            });

            // Registering package commands.
            $this->commands($this->commands);
//        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'arche');

        // Register the main class to use with the facade
        $this->app->singleton('arche', function () {
            return new Arche;
        });
    }
}
