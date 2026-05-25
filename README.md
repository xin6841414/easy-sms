# EasySms  for Laravel

使用 [overtrue/easy-sms](https://github.com/overtrue/easy-sms) 的laravel扩展包
在此基础上增加了对253云通讯(创蓝)v2的支持(Chuanglanv2Gateway)，如无需使用创蓝v2，请使用 [overtrue/laravel-easy-sms](https://github.com/overtrue/laravel-easy-sms)


## 安装

```shell
$ composer require xin6841414/easy-sms
```

## 配置

1. 在 config/app.php 注册 ServiceProvider (Laravel 5.5 + 无需手动注册)：

    ```php
    'providers' => [
        // ...
        Xin6841414\EasySms\EasySmsChannelServiceProvider::class,
    ],
    ```

2. 创建配置文件：

    ```shell
    $ php artisan vendor:publish --provider="Xin6841414\EasySms\EasySmsChannelServiceProvider"
    ```

3. 修改应用根目录下的 config/easysms.php 中对应的参数即可。
## 使用
 ```shell
        $mobile = '177xxx1234';
        $code = 123456;
        $template = '102xxx388'; // 模板ID 
         //内容为：您的验证码是{param1}，{param2}分钟内有效，您正在操作{param3}，请勿泄露。
        $gateways = ['chuanglanv2'];

         app('easysms')->send($mobile,
            [
                'template' => $template,
                'data' => [
                    'param1' => $code,
                    'param2' => 5,
                    'param3' => '登录'
                ]

            ], $gateways);
 ```
其他用法请参考 [overtrue/easy-sms](https://github.com/overtrue/easy-sms)

## 鸣谢
代码参考: [leonis/easysms-notification-channel](https://github.com/yl/easysms-notification-channel)