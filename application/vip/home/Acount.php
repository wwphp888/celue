<?php
namespace app\vip\home;

use app\vip\home\Index;

use think\Db;

use think\Request;
use think\helper\Hash;

class Acount extends Index{


	public function index(){



		return $this->fetch();
	}
	public function edit_password(){

		if(request()->isPost()){



			if(!Hash::check((string)input('post.password'),Db::name('vip')->where('id='.$this->uid)->value('vip_password'))){

				return  json(['status'=>3,'message'=>'密码不正确']);		

			}

			$ret = Db::name('vip')->where('id',$this->uid)->setField('vip_password',  Hash::make((string)input('post.newpass')));	


			if($ret){


				return  json(['status'=>2,'message'=>'修改成功']);		


			}else{
				return  json(['status'=>3,'message'=>'修改失败']);		
			}	






		}





	}
		public function edit_paypassword(){

				if(request()->isPost()){



					if(!Hash::check((string)input('post.password'),Db::name('vip')->where('id='.$this->uid)->value('vip_paypassword'))){

						return  json(['status'=>3,'message'=>'支付密码不正确']);		

					}

					$ret = Db::name('vip')->where('id',$this->uid)->setField('vip_paypassword',  Hash::make((string)input('post.newpass')));	


					if($ret){


						return  json(['status'=>2,'message'=>'修改成功']);		


					}else{
						return  json(['status'=>3,'message'=>'修改失败']);		
					}	

				}

			}

	public function idcardverify(){
		$parms = request()->param();

		if($parms['realname'] == ''){
			return  json(['status'=>0,'message'=>'真实姓名不能为空']);
		}
		if($parms['idcard'] == ''){
			return  json(['status'=>0,'message'=>'身份证号不能为空']);
		}
		$data['vip_realname'] = $parms['realname'];
		$data['vip_idcard'] = $parms['idcard'];
		$check = Db::name("vip")->where("vip_idcard",$data['vip_idcard'])->count('id');
		if($check > 0){
			return  json(['status'=>0,'message'=>'该身份证号码已存在，请联系客服处理']);
		}
		$check2 = Db::name("vip")->where("id",$this->uid)->value("vip_idcard");
		if($check2 != ''){
			return  json(['status'=>0,'message'=>'已实名认证不可更改，如需更改请联系客服处理']);
		}
		$res = Db::name("vip")->where("id",$this->uid)->update($data);
		if($res){
			return  json(['status'=>1,'message'=>'实名认证成功']);
		}else{
			return  json(['status'=>0,'message'=>'实名认证失败,请重试']);
		}


	}





}