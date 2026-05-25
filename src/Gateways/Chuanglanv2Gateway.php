<?php
/**
 * Created by PhpStorm.
 * User: xin6841414
 * Date: 5-22-022
 * Time: 9:39:50
 */

namespace Xin6841414\EasySms\Gateways;


use Illuminate\Support\Str;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

class Chuanglanv2Gateway extends Gateway
{
    use HasHttpRequest;

    //国际新加坡节点
    const INI_X_URL = 'https://sg-intapi.tig253.com/send/sms';
    //国际上海节点
    const INI_S_URL = 'https://intapi.tig253.com/send/sms';
    //国内节点
    const CHINA_URL = 'https://smssh.253.com/msg/sms/v2/tpl/send';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $timestamp = strval(time());
        $nonce = str_replace('-', '', Str::uuid());
        $data = $this->AuthenticationType($config, $timestamp, $nonce);
        $params = [
            'account' => $config->get('account'),
            'timestamp'=> $timestamp,
            'nonce' => $nonce,
            'phoneNumbers' => $to->getNumber(),
            'templateId' => $message->getTemplate(),
            'templateParamJson' => $this->assembleTemplateParams($message),
            'report' => $config->get('needstatus') ?? 'false',  //状态回执开关
            'callbackUrl' => $config->get('callback')?? '',// 状态回执的回调地址
        ];
        $result = $this->postJson($this->buildEndpoint($config, '86'), array_merge($params, $data['params']), $data['header'] ?? [] );

        if (!isset($result['code']) || '000000' != $result['code']) {
            throw new GatewayErrorException(json_encode($result, JSON_UNESCAPED_UNICODE), isset($result['code']) ? $result['code'] : 0, $result);
        }

        return $result;
    }

    protected  function AuthenticationType(Config $config, $timestamp, $nonce)
    {
        $header = [];
        $params = [];
        if ('sign' == $config->get('authenticate')) {
            // 选择签名认证方式
            $header = [
                'X-QA-Hmac-Signature' => $this->makeSignature($config->get('password'), $timestamp, $nonce),
            ];
        } elseif ('password' == $config->get('authenticate')) {
            // 选择password认证方式
            $params = [
                'password' => $config->get('password'),
            ];
        }
        return compact('header', 'params');
    }


    protected function buildEndpoint(Config $config, string $countryCode): string
    {
        if ('86' == $countryCode) {
            return self::CHINA_URL;
        } else {
            //未测试
            return self::INI_S_URL;
        }
    }

    protected function assembleTemplateParams(MessageInterface $message): string
    {
        $data = $message->getData();
        return json_encode([$data], JSON_UNESCAPED_UNICODE);
    }

    protected function makeSignature($password, $timestamp,$nonce): string
    {
        $md5Password = md5($password);
        $strData = $this->generateStr($md5Password, $timestamp, $nonce);
        // 使用hash_hmac函数生成 HMAC-SHA256签名
        return hash_hmac('sha256', $strData, $md5Password);
    }

    protected function generateStr($md5Password, $timestamp, $nonce): string
    {
        $array = array($md5Password, $timestamp, $nonce);
        sort($array, SORT_STRING);
        return implode($array);
    }
}
