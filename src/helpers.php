<?php
/**
 * Created by PhpStorm.
 * User: xin6841414
 * Date: 5-26-026
 * Time: 10:30:38
 */

use Illuminate\Support\Facades\Cache;

if (!function_exists('sms_code_check')){

    /**
     * 验证短信验证码是否正确
     * @param $validateCode string 短信校验码
     * @param $mobile  string  手机号码
     * @param $code  string  短信验证码
     * @return boolean
     */
    function sms_code_check($validateCode, $mobile, $code)
    {
        if (!$validateCode) {
            return false;
        }
        $data = Cache::get($validateCode);
        if (!$data) {
            return false;
        }
        if (!preg_match(config('easysms.mobile_validate.regex'),$mobile)) {
            return false;
        }
        if (strcasecmp($data['mobile'], $mobile)) {
            return false;
        }
        if (strcasecmp($data['code'], $code)) {
            return false;
        }
        Cache::forget($validateCode);
        return true;
    }
}
