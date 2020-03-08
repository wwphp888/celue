<?php
namespace plugins\Mdsms\model;

use app\common\model\Plugin;

/**
 * 后台插件模型
 * @package plugins\HelloWorld\model
 */
class Mdsms extends Plugin
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__MDSMS__';

    public function test()
    {
        // 获取插件的设置信息
        halt('test');
    }
}