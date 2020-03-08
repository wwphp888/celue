<?php
namespace app\api\controller;

use app\common\controller\Admin;
use think\Request;
use think\Db;

class Apicommon extends Admin{

	public $vip_info;
	public function _initialize(){

		parent::_initialize();
		$header = Request::instance()->header();
		$authKey = $header['authorization'];
if(empty($authKey) || $authKey == ''){
			$authKey = request()->param('token');
		}

		$cache = cache('Auth_'.$authKey);
		 // 校验sessionid和authKey
		
        if (empty($authKey)||empty($cache)) {
            //header("HTTP/1.1 999 token");
         	msgreturn('','登录已过期',999); 

            
        }
        cache('Auth_'.$authKey, $cache, 1644);
     	$this->vip_info =Db::name('vip')->field(['vip_password','vip_paypassword'],true)->where('id','=',$cache['vip_info']['id'])->find();   




	}

	public function index(){



		return msgreturn($this->vip_info,'');
	}


}