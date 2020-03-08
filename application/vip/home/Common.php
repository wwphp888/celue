<?php 
namespace app\vip\home;

use app\index\controller\Home;
use think\Db;
use think\Request;
use think\helper\Hash;
use app\vip\model\Vip as VipModel;
session_start();

class Common extends Home{




	public function _initialize(){

			parent::_initialize();

			$vipId = $_SESSION['vip_id'];
	
		
			if($vipId){



				$this->redirect("/vip");	


			}

	}

	public function login(){

		if(request()->isPost()){

			$info = Db::name('vip')->where('vip_name',input('post.vip_username'))->find();	
			if(!$info){

				return json(['status'=>3,'message'=>'用户不存在']);

			}
			if(!Hash::check((string)input('post.vip_password'),$info['vip_password'])){

				return json(['status'=>3,'message'=>'密码不正确']);

			}
			Db::name('vip')->where('id',$info['id'])->setField('last_login_time',time());
			$_SESSION['vip_id'] =$info['id'];
			$_SESSION['vip_name'] = $info['vip_name'];
			return json(['status'=>2,'message'=>'登陆成功']);

		}else{
		 return $this->fetch();
		}




	}

	
	public function reg(){

		if(request()->isPost()){

			$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>input('post.vip_phone')  ,'code'=>input('post.phone_code')]);
			if($res['status']!=2){

				return  json(['status'=>3,'message'=>$res['message']]);	

			}
			//print_r(input('post.'));exit;
			$data = input('post.');
			$data['vip_name'] = $data['vip_phone'];
			$data['register_time'] = time();
			$data['vip_paypassword'] = $data['vip_password'];
            $agent=Db::name('agent')->where('agent_code',$data['recommendCode'])->find();   
            /*if(!$agent){
            
            	return json(['status'=>3,'message'=>'邀请码不存在']);
            } */
			$result = $this->validate($data, 'Vip.reg');
			
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


		}else{
			return $this->fetch();
		}


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

	public function clickreg(){
		$info = get_advert(1);
		return $info;
	}




}