<?php
namespace app\vip\home;

use app\vip\home\Index;

use think\Db;

use think\Request;

class Bank extends Index{


	public function index(){
		$info = Db::name("vip")->where("id",$this->uid)->find();
		
		if($info['vip_idcard'] == '' || $info['vip_realname'] == ''){
			$this->error("请先实名认证","/vip/acount");
		}
		$this->assign("info",$info);
		$banklist = Db::name("banklist")->where("status",1)->select();
		$this->assign("banklist",$banklist);
		$bankinfo = Db::name("vip_bank")->where("bank_vip",$this->uid)->find();
		$this->assign("bankinfo",$bankinfo);

		return $this->fetch();
	}

public function addbank(){
	$vip_phone = Db::name("vip")->where("id",$this->uid)->value("vip_phone");
	$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>$vip_phone,'code'=>input('post.phone_code')]);
	if($res['status']!=2){
		return  json(['status'=>0,'message'=>$res['message']]);	
	}
	$prams = input('post.');
	$data['bank_vip'] = $this->uid;
	$data['bank_name'] = $prams['bankname'];
	$data['bank_number'] = $prams['bankcard'];
	$data['bank_time'] = time();
	$check = Db::name("vip_bank")->where("bank_number",$data['bank_number'])->count("id");
	if($check > 0){
		return  json(['status'=>0,'message'=>'该银行卡已存在，如需修改请联系客服处理']);	
	}
	$result = Db::name("vip_bank")->insert($data);
	if($result){
		return  json(['status'=>1,'message'=>'绑定成功']);	
	}else{
		return json(['status'=>0,'message'=>'绑定失败']);	
	}


}


public function sendcode(){

		if(Request::instance()->isPost()){
			$vip_phone = Db::name("vip")->where("id",$this->uid)->value("vip_phone");
			$code = rand(100000,999999);
			$res=plugin_action('Mdsms', 'Mdsms', 'sendsms', ['phone' =>$vip_phone ,'code'=>$code,'msg'=>'您的验证码是'.$code.',千万别告诉别人,谨防盗用']);
			if($res['status']==2){

				return json(['status'=>2,'message'=>'发送成功']);	
			}else{


				return json(['status'=>3,'message'=>$res['message']]);	

			}
			
		}

	}

}