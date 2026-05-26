<?php
/**
 * Created by PhpStorm.
 * User: xin6841414
 * Date: 2019/6/4 0004
 * Time: 上午 8:54
 */

namespace Xin6841414\EasySms\Traits;


trait RegularValidate
{

    /**
     * 检查手机号是否正确
     * @param $mobile
     * @return false|int
     */
    public function checkMobile($mobile)
    {
        //临时校验
        return preg_match(config('easysms.mobile_validate.regex'),$mobile);
    }

}