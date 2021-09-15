<?php

namespace Scaffolding;

use Illuminate\Support\ServiceProvider;
use Scaffolding\Database\ScaffoldMigrationCreator;

class ScaffoldingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/scaffold.php', 'scaffold');
        
        $this->app->singleton(ScaffoldMigrationCreator::class, function ($app) {
            return new ScaffoldMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }

    public function boot()
    {
        $this->configureCommands();
        $this->configurePublishing();
    }

    public function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }
        
        $this->commands([
            \Scaffolding\Console\ScaffoldCommand::class,
            \Scaffolding\Console\ScaffoldModelCommand::class,
            \Scaffolding\Console\ScaffoldMigrationCommand::class,
            \Scaffolding\Console\ScaffoldControllerCommand::class,
            \Scaffolding\Console\ScaffoldMakeViewCommand::class,
            \Scaffolding\Console\ScaffoldResourceCommand::class,
        ]);
    }

    public function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }
        
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'scaffold-stubs');

    }
}