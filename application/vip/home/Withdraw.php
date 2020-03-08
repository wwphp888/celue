<?php
namespace app\vip\home;


use app\vip\home\Index;
use think\Db;
use think\Request;
use think\helper\Hash;
class Withdraw extends Index
{
	
	public function index(){

		$bankinfo = Db::name('vip_bank')->where('bank_vip='.$this->uid)->find();
		if(!is_array($bankinfo)|| !isset($bankinfo['id'])){
			$this->error("请先绑定银行卡","/vip/bank");
		}
		$this->assign('bankinfo',$bankinfo);
	     return $this->fetch(); // 渲染模板
	}
	public function log(){

		$map['withdraw_vip']= $this->uid;
		
		if(input('time')){


			$map['withdraw_time'] =array('gt',date("Y-m-d",strtotime("-".input('time')." month")));
		}
		
		$status_name = array(0=>'待审核',1=>'成功',2=>'失败');
		
		
		$list = Db::name('vip_withdraw')->where($map)->order("id desc")->paginate(10)->each(function($item,$key)use($status_name){
			$item['status_name'] = $status_name[$item['withdraw_status']];
			return $item;
		});

 		$this->assign('list', $list);//单独提取分页出来
		$this->assign('page', $list->render());//单独提取分页出来

		 return $this->fetch(); // 渲染模板


	}
	public function sendcode(){

		if(Request::instance()->isPost()){
			$code = rand(000000,999999);
			$res=plugin_action('Mdsms', 'Mdsms', 'sendsms', ['phone' =>Db::name('vip')->where('id='.$this->uid)->value('vip_phone') ,'code'=>$code,'msg'=>'您的提现验证码是'.$code.',千万别告诉别人']);
			if($res['status']==2){

				return json(['status'=>2,'message'=>'发送成功']);	
			}else{


				return json(['status'=>3,'message'=>$res['message']]);	

			}
			



		}


	}
		public function submitwithdraw(){

		if(Request::instance()->isPost()){

				

			$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>Db::name('vip')->where('id='.$this->uid)->value('vip_phone')  ,'code'=>input('post.vip_phone')]);
			/*if($res['status']!=2){

				return  json(['status'=>3,'message'=>$res['message']]);	

			}*/
			if(!Hash::check((string)input('post.vip_paypassword'),Db::name('vip')->where('id='.$this->uid)->value('vip_paypassword'))){

				return  json(['status'=>3,'message'=>'支付密码不正确']);		

			}
			$bankinfo = Db::name('vip_bank')->where('bank_vip='.$this->uid)->find();
			if(!$bankinfo){

				return  json(['status'=>3,'message'=>'没有你的银行卡信息']);		
			}
			$data['withdraw_vip'] = $this->uid;
			$data['withdraw_amount'] = input('post.withdraw_money');
			$data['withdraw_time'] = time();
			$data['withdraw_card'] =  $bankinfo['bank_number'];
			$data['withdraw_realname'] =  Db::name('vip')->where('id='.$this->uid)->value('vip_realname');
			$data['withdraw_bank'] =  $bankinfo['bank_name'];
			Db::startTrans();
			try{
				Db::name('vip_withdraw')->insertGetId($data);
				money_log($data['withdraw_amount'],$data['withdraw_vip'],2,'提现扣除金额');
				Db::commit();
				return json(['status'=>2,'message'=>'提现申请成功']);
			}catch (\Exception $e) {


				 Db::rollback();
				 return json(['status'=>3,'message'=>'提现申请失败']);
			}


			/*Db::transaction(function()use($data){
			if(!Db::name('vip_withdraw')->insertGetId($data)||!money_log($data['withdraw_amount'],$data['withdraw_vip'],2,'提现扣除金额')){
				return json(['status'=>3,'message'=>'提现申请失败']);
			}
			Db::commit();
			return json(['status'=>2,'message'=>'提现申请成功']);
			});*/
			
			//return json(['status'=>3,'message'=>'提现申请失败']);
		}


	}
}