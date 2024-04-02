<?php

namespace MCris112\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use MCris112\Settings\SettingsService;

class Settings extends Facade
{

    protected static function getFacadeAccessor()
    {
        return SettingsService::class;
    }
}
