<?php
/**
 * Created by PhpStorm.
 * User: xin6841414
 * Date: 2019/6/4 0004
 * Time: 上午 8:44
 */

namespace Xin6841414\EasySms\Traits;

use Xin6841414\EasySms\Events\ExceptionCustomEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Log;

trait Sms
{
    use RegularValidate;


    public function returnArr($msg = '', $code = false, array $data = [])
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 发送短信
     * @param $mobile integer 手机号码
     * @param int $template 自定义模板id  0,1,2,3  详见配置文件 config/easysms.php 配置文件sms_templates配置项
     * @param $parameter array 模板参数 模板参数，如：['code' => '1234']
     * @param string[] $gateways 网关名称.支持多个，循环使用直到成功
     * @return array
     * @throws \Exception
     */
    public function sendSms($mobile, $template = 0, array $parameter = [], $gateways = ['chuanglanv2'])
    {

        if (!$this->checkMobile($mobile)) {
            return $this->returnArr(config('easysms.mobile_validate.error_message', '手机号码格式错误！'));
        }
        //生成随机数，
        $code = $this->generateRandomNumber(config('easysms.code.length'));
        $data =[
            'prev_time' => time()
        ];
        $prev_mobile_data = Cache::get('auth_mobile_info_'.$mobile);
        if ($prev_mobile_data && time()-$prev_mobile_data['prev_time'] < config('easysms.code.interval')){
            //短信发送太频繁
            return $this->returnArr('短信发送太频繁，请稍后再试！');
        }
        Cache::put('auth_mobile_info_'.$mobile, $data, 3600);
        try {
            $key = 'verificationCode_'.Str::random(15);
            //缓存验证码
            Cache::put($key, ['mobile'=> $mobile, 'code'=>$code], config('easysms.code.expire'));
            $data['verificationCode_key'] = $key;
            if (config('easysms.code.dev_switch') && app()->environment('local')){
                return $this->returnArr('测试短信验证码('.$code.')发送成功！', true, $data);
            }
            $result = app('easysms')->send($mobile,
                $this->smsTemplate(array_merge(['code' => $code],$parameter), $template), $gateways);
            foreach($gateways as $gateway) {
                if ($result[$gateway]['status'] == 'success') {
                    $data['smsGateway'] = $gateway;
                    break;
                }
                if ($result[$gateway]['status'] == 'failure') {
                    //短信网关异常，需要通知报告
                    event(new ExceptionCustomEvent($result[$gateway]['exception'], '发送短信'));
                }
            }
            return $this->returnArr('短信已发送', true, $data);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $e) {
            event(new ExceptionCustomEvent($e, '发送短信'));
            return $this->returnArr('所有短信服务商服务异常，发送失败！');
        }
    }

    public function generateRandomNumber( $length) {
        $characters = config('easysms.code.characters');
        $randomString = '';
        for ( $i = 0;  $i <  $length;  $i++) {
             $index = rand(0, strlen( $characters) - 1);
             $randomString .=  $characters[ $index];
        }
        return  $randomString;
    }

    /**
     * 短信内容模板
     * @param $code
     * @param $templateId
     * @return array
     * @throws \Exception
     */
    public function smsTemplate(array $parameter, $templateId)
    {
        if (in_array($templateId, array_keys(config('easysms.sms_templates')))) {
//            if (isset(config('easysms.sms_templates')[$templateId]['params_number'])
//                && config('easysms.sms_templates')[$templateId]['params_number'] != count($parameter)
//            ) {
//                //传入的参数个数和模板参数个数不一致
//                throw new \Exception('传入的参数个数和模板参数个数不一致');
//            }

            $data = config('easysms.sms_templates')[$templateId]['data'];
            $keys = array_keys(config('easysms.sms_templates')[$templateId]['data']);
            $values = array_values($parameter);
            for ($i = 0; $i < count($keys); $i++) {
                if (isset($values[$i])) {
                    $data[$keys[$i]] = $values[$i];
                }
            }
            $content = '';
            if (isset(config('easysms.sms_templates')[$templateId]['content']) && $data){
                $content = vsprintf(config('easysms.sms_templates')[$templateId]['content'], $data);
            }
            $template = config('easysms.sms_templates')[$templateId]['template'];
            return compact('template','content', 'data');
        }
        throw new \Exception('短信模板不存在');
    }

    /**
     * 保存短信发送报告
     * @param $result
     * @return bool
     */
    public function setSMSStatus($result)
    {
        //demo
//        $result = [
//            'receiver' => null,
//            'pswd' => null,
//            'msgid' => 19051517563227554,
//            'reportTime' => 1905151756,
//            'mobile' => 13070830627,
//            'status' => 'DISTURB',
//            'notifyTime' => 190515175632,
//            'statusDesc' => '发送条数超限制',
//            'length' => 1
//        ];
        try {
            if ($result['status'] != 'DELIVRD') {
                $data = [
                    'code' => false,
                    'msg' => $result['statusDesc'],
                ];

            } else {
                $data = [
                    'code' => true,
                    'msg' => $result['statusDesc'],
                ];
            }
            Cache::put('count'.$result['mobile'], $data, 24*60);
            return true;
        }catch (\Exception $e) {
            return false;
        }
    }


    /**
     * 获取短信发送报告,请在短信发送一段时间后检查
     * @param $mobile
     * @return array|mixed
     */
    public function getSMSStatus($mobile)
    {
        $result = Cache::get('count'.$mobile);
        if (!$result) {
            //未推送情况下 默认正常！
            return $this->returnArr('短信发送正常', true);
        }
        return $result;
    }

}
