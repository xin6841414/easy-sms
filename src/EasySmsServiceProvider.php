<?php



namespace Xin6841414\EasySms;

use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;
use Illuminate\Validation\Factory;

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
        /* @var Factory $validator */
        $validator = $this->app['validator'];
        // 加载辅助函数文件
        $this->loadHelpers();
        // Validator extensions
        $validator->extend('sms_code_check', function ($attribute, $value, $parameters) {
            return sms_code_check($parameters[0],$parameters[1], $value);
        });
        $validator->replacer('sms_code_check', function ($message, $attribute, $rule, $parameters) {
            return __('The SMS verification code is incorrect or has expired');
        });

    }
    protected function loadHelpers()
    {
        foreach (glob(__DIR__ . '/Helpers/*.php') as $filename) {
            require_once $filename;
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
