<?php

namespace app\trade\validate;

use think\Validate;

/**
 * 配置验证器
 * @package app\admin\validate
 */
class Gupiao extends Validate
{
    protected $rule =   [
        'quota'  => 'require|number|max:10', 
    ];
    
    protected $message  =   [
        'quota.require' => '限额不能为空，如无需设置请填写0',
        'quota.max'     => '限额最多不能超过10个字符',
        'quota.number'   => '限额必须是数字', 
    ];
    
    protected $scene = [
        'quota'  =>  ['quota'],
    ];
}