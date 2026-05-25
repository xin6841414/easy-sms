<?php



namespace Xin6841414\EasySms;

use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class EasySmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/easysms.php' => config_path('easysms.php'),
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/easysms.php', 'easysms');

        $this->app->singleton(EasySms::class, function () {
            $config = config('easysms');
            $easySms = new EasySms($config);

            foreach ($config['custom_gateways'] as $name => $gateway) {
                $easySms->extend($name, function ($gatewayConfig) use ($gateway) {
                    return new $gateway($gatewayConfig);
                });
            }

            return $easySms;
        });

        $this->app->alias(EasySms::class, 'easysms');
    }
}
