<?php

namespace app\agent\controller;
use com\verify\HonrayVerify;
use app\common\controller\Admin;
use think\Request;
use think\Db;
use think\helper\Hash;

class Base extends Admin{


		public function getVerify(){

			 $captcha = new HonrayVerify(config('agent_captcha'));
        	 return $captcha->entry();
        	

		}
		public function login()
	    {   
	     if (Request::instance()->isPost()){
	    	$captcha = new HonrayVerify(config('agent_captcha'));
	    	$param = $this->param;
	    	if (!$captcha->check($param['verifyCode'])) {

	    		 msgreturn('','验证码错误');
	    		

            }
            $map['agent_username']  =$param['username'];
	    	$agent_info =Db::name('agent')->where($map)->find();
	    	if(!$agent_info){

	    		 msgreturn('','用户不存在');


	    	}

	    	//print_r(Hash::make((string)$param['password']));exit;
	    	if (!Hash::check((string)$param['password'],$agent_info['agent_password'])) {
				 msgreturn('','密码错误');
    		}

	    	// 保存缓存        
	        session_start();
	        $info['agent_info'] = $agent_info;
	        $info['sessionId'] = session_id();
	        $authKey = Hash::make((string)$agent_info['agent_username'].$agent_info['agent_password'].$info['sessionId']);
	        $info['authKey'] = $authKey;
	        cache('Auth_'.$authKey, null);
	        cache('Auth_'.$authKey, $info, 1644);
	        // 返回信息
	        $data['authKey']		= $authKey;
	        $data['sessionId']		= $info['sessionId'];
	        $data['agent_info']		= $agent_info;
	         msgreturn($data,'');

	     }	

	    }
	    public function loginout(){


	    	 $authkey = Request::instance()->header('authorization');
	    	 
	         cache('Auth_'.$authkey, null);




	    }



}