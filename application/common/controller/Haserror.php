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

namespace app\common\controller;

use think\Controller;
use think\exception\Handle;

/**
 * 项目公共控制器
 * @package app\common\controller
 */
class Haserror extends Handle
{
	public function render(\Exception $e){
		if(config('app_debug')){
			return parent::render($e);
		}else{
			header('Location:'.url('@index/error/index'));
		}
	}
}