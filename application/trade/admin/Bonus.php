<?php 

namespace app\trade\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\trade\model\Log as LogModel;
use think\Db;
use think\Hook; 
use think\Cache;
use think\Request;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class Bonus extends admin{

	//今日公告
	public function index(){
		return ZBuilder::make('table')
        	
            ->fetch(); // 渲染模板
	}
	//近期除权记录
	public function log(){
		return ZBuilder::make('table')
        	
            ->fetch(); // 渲染模板
	}
	//待除权股票
	public function wait(){
		return ZBuilder::make('table')
        	
            ->fetch(); // 渲染模板
	}
	//拆零股票管理
	public function surplus(){
		return ZBuilder::make('table')
        	
            ->fetch(); // 渲染模板
	}
}