<?php



return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'chuanglanv2',
        ],
    ],

    // 可用的网关配置
    'gateways' => [

        // 云片
        'yunpian' => [
            'api_key' => 'efabf**********************20fd3',
        ],

        // ...
    ],

    'custom_gateways' => [
        'chuanglanv2' => \Xin6841414\EasySms\Gateways\Chuanglanv2Gateway::class,
    ],
    'mobile_validate' => [
        'regex' => env('MOBILE_REGEX', '/^1[3456789]\d{9}$/'),
        'error_message' => env('MOBILE_ERROR', '手机号码格式不正确'),
    ],
    'code' => [
        'length' => env('SMS_CODE_LENGTH', 4),
        'characters' => env('SMS_CODE_CHARACTERS', '0123456789'),
        'expire' => env('SMS_CODE_EXPIRE', 300),   // 过期时间，单位秒
        'interval' => env('SMS_CODE_INTERVAL', 60), // 短信时间间隔，单位秒
        'dev_switch'=> env('SMS_CODE_DEV', true), // 开发模式，不发送短信
    ],
    'sms_templates' => [
        [
            'template' => env('SMS_TEMPLATE_ID_0', '1022118388'), // 短信运营商模板ID1
            'content' => env('SMS_TEMPLATE_CONTENT_0', '【XX公司】您的验证码是%s, %s分钟内有效，您正在%s, 请勿将验证码告知他人。'),
            'params_number'=> 3, // 模板参数个数
            'data' => [
                'param1' => '1234',
                'param2' => 5,
                'param3' => '其他',
            ]
        ],
        [
            'template' => env('SMS_TEMPLATE_ID_1', '1022118388'), // 短信运营商模板ID2
            'content' => env('SMS_TEMPLATE_CONTENT_1', '【XX公司】您的验证码是%s, 5分钟内有效, 请勿将验证码告知他人。'),
            'params_number'=> 1, // 模板参数个数
            'data' => [
                'param1' => '1234',
            ]
        ]
    ]



];
