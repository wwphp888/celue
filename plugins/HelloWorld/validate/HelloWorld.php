<?php
// +----------------------------------------------------------------------
// | 股票策略系统 [ V1.02 ]
// +----------------------------------------------------------------------
// | 版权所有 2018~2022 山东软淘电子商务有限公司 [ http://www.sdruantao.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.sdruantao.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace plugins\HelloWorld\validate;

use think\Validate;

/**
 * 后台插件验证器
 * @package app\plugins\HelloWorld\validate
 */
class HelloWorld extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|出处' => 'require',
        'said|名言' => 'require',
    ];
}
