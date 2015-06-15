<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 14.06.15
 * Time: 22:28
 */

namespace SMSkin\VKService\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class VKServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Config file path
        $configFile = __DIR__ . '/../../resources/config/vksettings.php';
        // Merge files
        $this->mergeConfigFrom($configFile, 'vksettings');
        // Publish
        $this->publishes([
            $configFile => config_path('vksettings.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
