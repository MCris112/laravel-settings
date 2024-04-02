<?php

namespace MCris112\Settings;

use App\Http\Resources\One\settings\SettingCurrencyResource;
use App\Models\System\CriCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use MCris112\Settings\Models\SettingsModel;

/**
 * @property array $site;
 * @property array $system;
 * @property array $user;
 * @property array $currencies;
 * @property array $logos;
 */
class SettingsService
{
    const CACHE_NAME = 'settings';

    const CACHE_SITE = 'settings.site';

    const CACHE_SYSTEM = 'settings.system';

    const CACHE_USER = 'settings.user';

    const CACHE_CURRENCIES = 'settings.currencies';

    const CACHE_LOGOS = 'settings.logos';

    protected array $sections = [
        'site' => SettingsService::CACHE_SITE,
        'system' => SettingsService::CACHE_SYSTEM,
        'user' => SettingsService::CACHE_USER,
        'currencies' => SettingsService::CACHE_CURRENCIES,
        'logos' => SettingsService::CACHE_LOGOS,
    ];

    function __get(string $name): Model|array|null
    {
        $key = $this->getCacheKey($name);

        if(!$key) return null;

        if($key == SettingsService::CACHE_CURRENCIES) return $this->currencies();

        if($key == SettingsService::CACHE_LOGOS) return $this->getLogoImagesUrls();

        $settings =  $this->iterate([ $this->section($name) ]);

        return $settings[$name];
    }

    public function all(): array
    {
        $arr = [];
        foreach ($this->sections as $section => $value) {
            $arr = [
                ...$arr,
                $section => $this->__get($section)
            ];
        }
        return $arr;
    }

    public function section(string $name): SettingsModel | null
    {
        $key = $this->getCacheKey($name);
        if(!$key) return null;

        /** @var SettingsModel $settings */
        $settings = Cache::rememberForever( $key, function() use( $name ){
            return SettingsModel::root()->where('key', $name)->withChildren()->first();
        } );

        if(!$settings) throw new \Exception('Configuracion invalida');

        return $settings;
    }

    protected function getCacheKey($section): string|null
    {
        return $this->sections[$section] ?? null;
    }

    protected function iterate(array|\Illuminate\Database\Eloquent\Collection $elements, $parentId = null): array
    {
        $tree = [];

        foreach ($elements as $element) {
            if( $element->parent_key == $parentId )
            {

                if( isset($element->children) && count($element->children) > 0 )
                {
                    $children = $this->iterate($element->children, $element->id);
                    $tree[$element->key] = $children;

                }else{
                    /**@var string|int|bool $data */
                    $data = $element->value;

                    try{
                        $decoding = json_decode($data, true);

                        if( $decoding ) $data = $decoding;
                        // $data = $decoding;
                    }catch(\Exception $e){}

                    if( $data == "[]") $data = [];

                    if( empty($data) && $data != []) $data = 0;

                    $tree[$element->key] = $data;
                }

            }
        }

        return $tree;
    }

    /**
     * Update settings
     *
     * @param string $sectionName The name of the section settings
     * @param array $data Has to be the exact herarchy of the settings section name to can actually work correctly
     * @return array
     */
    public function update(string $sectionName, $data): array
    {
        $updated = $this->updateSettings( $this->section($sectionName), $data);

        $this->clear(); //Clear cache for new settings

        return $updated;
    }

    private function updateSettings(SettingsModel $settings, $data)
    {

        if( count($settings->children) > 0 && is_array($data))
        {
            $children = [];
            foreach ( $settings->children as $child)
            {
                // If $child->key exist in the data, so replace it with new value
                if(isset($data[$child->key]))
                $children[$child->key] = $this->updateSettings($child, $data[$child->key]);
            }

            return $children;
        }

        $prevalue= $settings->value;
        if(is_array($data))
        {
            $settings->value = json_encode($data, true);
        }else{
            $settings->value = $data;
        }

        //if it's not the same value, so saved.
        if($prevalue != $settings->value)
        $settings->save();

        return $settings;
    }

    protected function currencies()
    {
        $currencies = Cache::remember('currencies', now()->addYear(), function() {
            return CriCurrency::all();
        });

        return json_decode(SettingCurrencyResource::collection($currencies)->toJson(), true);
    }

    public function getLogoImagesUrls()
    {
        return [
            'normal' =>  config('app.url').'/'.'storage/logo/normal',
            'white' => config('app.url').'/'.'storage/logo/white',

            'text' => config('app.url').'/'.'storage/logo/text',
            'textWhite' => config('app.url').'/'.'storage/logo/textWhite',
        ];
    }

    public function clear(): void
    {
        foreach ($this->sections as $section) {
            Cache::forget($section);
        }
    }
}
