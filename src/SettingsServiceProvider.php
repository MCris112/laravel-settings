<?php

namespace MCris112\Settings;
class SettingsServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot(): void
    {
        $this->registerMigrations();
    }

    protected function registerMigrations(): void
    {
        if(!$this->app->runningInConsole()) return;
        $this->loadMigrationsFrom( dirname(__DIR__) . '/database/migrations' );
    }
}
