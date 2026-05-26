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
        Xin6841414\EasySms\EasySmsServiceProvider::class,
    ],
    ```

2. 创建配置文件：

    ```shell
    $ php artisan vendor:publish --provider="Xin6841414\EasySms\EasySmsServiceProvider"
    ```

3. 修改应用根目录下的 config/easysms.php 中对应的参数即可。
## 使用
### 1. 在控制器中使用
```shell
namespace App\Http\Controllers;

use Xin6841414\EasySms\Traits\Sms;
    class TestController extends Controller
{
    use Sms;
     public function index()  
     {
       $mobile = '177xxx1234';
       $template = '0'; // 模板ID非短信运营商短信模板ID, 在config/easysms.php中配置sms_templates项
       $parameter = [
        'code' => 123456,
        'time' => 5,
       ];  // 模板参数, 对应模板中的参数,按顺序传入任意数量, key键任意定义,代码会顺序替换为模板中的键名
       $gateways= ['chuanglanv2']; // 指定发送的短信服务商, 名称来自config/easysms.php中gateways项
       $result = $this->sendSms($mobile,$template,$parameter,$gateways);
       //['code' => true, 'msg' => '短信已发送', 'data' =>[
       'verificationCode_key'=>'verificationCode_sfsdfsdserewxxer',
       'prev_time'=> 1779772561 //上次请求发送短信的时间戳,用户限流,间隔时间配置在config/easysms.php中code.interval项
       ]]
     } 
     
     public function store(Request $request)
     {
       //扩展了验证规则sms_code_check, 用于校验验证码是否正确
       $request->validate([
           'mobile' => 'required|regex:/^1[3456789]\d{9}$/', //手机号
           'verificationCode_key' => 'required', //验证码key,即上一步发送短信返回的key
            'code' => 'required|sms_code_check:verificationCode_key,mobile',
        ]);
        ...
      } 
}
    
```
## 在其他处使用
如果不使用trait的话，可以直接使用以下简单调用
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
如果需要处理异常或者发送失败通知，可以监听Xin6841414\EasySms\Events\ExceptionCustomEvent事件：

其他用法请参考 [overtrue/easy-sms](https://github.com/overtrue/easy-sms)

## 鸣谢
代码参考: [leonis/easysms-notification-channel](https://github.com/yl/easysms-notification-channel)