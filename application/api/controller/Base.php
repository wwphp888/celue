<?php

namespace app\api\controller;

use com\verify\HonrayVerify;
use app\common\controller\Admin;
use think\Request;
use think\Db;
use think\helper\Hash;
use app\vip\model\Vip as VipModel;
class Base extends Admin{


		//验证码获取****GET
		public function getVerify(){

			 $captcha = new HonrayVerify(config('agent_captcha'));
        	 return $captcha->entry();
        	

		}
		//登录 post
		public function login()
	    {   
	     if (Request::instance()->isPost()){
	    	//$captcha = new HonrayVerify(config('agent_captcha'));
	    	$param = $this->param;
	    	/*if (!$captcha->check($param['verifyCode'])) {

	    		 msgreturn('','验证码错误');
	    		

            }*/
            $map['vip_phone']  =$param['username'];
	    	$vip_info =Db::name('vip')->where($map)->find();
	    	if(!$vip_info){

	    		 msgreturn('','用户不存在');


	    	}

	    	//print_r(Hash::make((string)$param['password']));exit;
	    	if (!Hash::check((string)$param['password'],$vip_info['vip_password'])) {
				 msgreturn('','密码错误');
    		}

	    	// 保存缓存        
	        session_start();
	        $info['vip_info'] = $vip_info;
	        $info['sessionId'] = session_id();
	        $authKey = Hash::make((string)$vip_info['vip_name'].$vip_info['vip_password'].$info['sessionId']);
	        $info['authKey'] = $authKey;
	        cache('Auth_'.$authKey, null);
	        cache('Auth_'.$authKey, $info, 644);
	        // 返回信息
	        $data['authKey']		= $authKey;
	        $data['sessionId']		= $info['sessionId'];
	        //$data['agent_info']		= $vip_info;
	         msgreturn($data,'');

	     }	

	    }
	    //register
	   	public function register(){



	   		if(request()->isPost()){

			$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>input('post.vip_phone')  ,'code'=>input('post.phone_code')]);
			if($res['status']!=2){

				return  json(['status'=>3,'message'=>$res['message']]);	

			}

			$data = input('post.');
			$data['vip_name'] = $data['vip_phone'];
			$data['register_time'] = time();
           $agent=Db::name('agent')->where('agent_code',$data['recommendCode'])->find(); 
          /*  if(!$agent){
            
            	return json(['status'=>3,'message'=>'邀请码不存在']);
            } */
			$result = $this->validate($data, 'vip/Vip.reg');
		
            // 验证失败 输出错误信息
            if(true !== $result) {


            	return  json(['status'=>3,'message'=>$result]);	

            }

            $user = VipModel::create($data);
            if($user){
            	return  json(['status'=>2,'message'=>'注册成功']);	

            }else{

            	return  json(['status'=>3,'message'=>'注册失败']);	
            }


		}








	   	}
	    //退出
	    public function loginout(){


	    	 $authkey = Request::instance()->header('authorization');
	    	 
	         cache('Auth_'.$authkey, null);




	    }
	   public function sendcode(){

		if(Request::instance()->isPost()){

			$code = rand(000000,999999);
			$res=plugin_action('Mdsms', 'Mdsms', 'sendsms', ['phone' =>input('post.vip_phone') ,'code'=>$code,'msg'=>'您的注册验证码是'.$code.',千万别告诉别人,谨防盗用']);
			if($res['status']==2){

				return json(['status'=>2,'message'=>'发送成功']);	
			}else{


				return json(['status'=>3,'message'=>$res['message']]);	

			}
			



		}




	}


}