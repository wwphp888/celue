<?php
namespace app\api\controller;

use app\api\model\User as UserModel;
use app\index\model\Invest as InvestModel;
use app\api\controller\Apicommon;
use think\Db;
use think\helper\Hash;
use think\Request;
class user extends Apicommon{




	public function inde(){






		return msgreturn($this->vip_info,'');



	}
	public function vip_info(){

		$data=[
			'gethas'=>gethascicang2($this->vip_info['id']),
			'money'=>['money'=>$this->vip_info['vip_money'],'freeze'=>$this->vip_info['vip_freeze']],
			'vip_phone'=>substr_replace($this->vip_info['vip_phone'],'*****',3,4),
			'vip_realname'=>$this->vip_info['vip_realname'],
          	'vip_head'=>"uploads/".$this->vip_info['head_img'],
			'vip_bank'=>Db::name('vip_bank')->where('bank_vip','=',$this->vip_info['id'])->count(),

		];


		return msgreturn($data,'');

	}
	public function vip_data(){


		$bank_number=Db::name('vip_bank')->where('bank_vip','=',$this->vip_info['id'])->value('bank_number');
		$data=[
			'vip_phone'=>substr_replace($this->vip_info['vip_phone'],'*****',3,4),
			'vip_idcard'=>$this->vip_info['vip_idcard']?substr_replace($this->vip_info['vip_idcard'],'*****',3,8):'',
			'vip_realname'=>$this->vip_info['vip_realname'],
			'vip_bank'=>$bank_number?substr_replace($bank_number,'********',3,8):'',

		];


		return msgreturn($data,'');

	}
	public function trade_list(){

	  //$status = array("in",'1,2');
      $status = 2;
      $list = Db::name("trade_order")->where("user_id",$this->vip_info['id'])->where("status",$status)->order("id desc")->select();
      foreach ($list as $k => $v) {
       
        $list[$k]['has_day'] = floor((time()-$v['creat_time'])/86400); //已持仓天数，向下取整
       // $list[$k]['has_day'] = ; //已持仓天数，向下取整
        $list[$k]['yingkui'] = round(floatval(($v['now_price']-$v['trush_price'])*$v['trush_number']),2); //当前盈亏
        $list[$k]['winstop'] = module_config('trade.winstop'); //当前盈亏
        $list[$k]['downstop'] = module_config('trade.downstop'); //当前盈亏
        if($list[$k]['yingkui'] < 0){
           $list[$k]['stop_win_price'] = number_format($v['now_price']-$v['stop_down'],2,'.','');
        }else{
           $list[$k]['stop_win_price'] = number_format($v['stop_win'] - $v['now_price'],2,'.','');
        }
        $list[$k]['create_times'] = date("Y-m-d H:i:s",$v['creat_time']);
        if($v['sell_time'] > 1){
           $list[$k]['sell_times'] = date("Y-m-d H:i:s",$v['sell_time']);
        }
        if($v['sell_type'] >0){
          $list[$k]['sell_types'] = $v['sell_type'] == 1?'自动卖出':'手动卖出';
        }
       
      }
      return msgreturn($list,'');

	}
  	public function apply_list(){

	  $status = 1;
      $list = Db::name("trade_order")->where("user_id",$this->vip_info['id'])->where("status",$status)->order("id desc")->select();
		foreach($list as $k => $v){
        
        	$list[$k]['creat_time'] = date('Y-m-d h:i:s',$v['creat_time']);
        
        }
       
    
      return msgreturn($list,'');

	}
    public function history_trade(){
    
    		$map['status'] = 4;
     		 $map['user_id'] =$this->vip_info['id'];
    		$page = input('post.page')?input('post.page'):1;
			$list = Db::name('trade_order')->field('user_id',true)->where($map)->page($page,10)->select();
			
			foreach ($list as $k => $v) {
		        $list[$k]['has_day'] = floor((time()-$v['create_time'])/86400); //已持仓天数，向下取整
		        $list[$k]['yingkui'] = round(($v['now_price']-$v['trush_price'])*$v['trush_number'],2); //当前盈亏
		        if($list[$k]['yingkui'] < 0){
		           $list[$k]['stop_win_price'] = round($v['now_price']-$v['stop_down'],2);
		        }else{
		           $list[$k]['stop_win_price'] = round($v['stop_win'] - $v['now_price'],2);
		        }
		        $list[$k]['create_times'] = date("Y-m-d",$v['creat_time']);
		        $list[$k]['create_time_miao'] = date("Y-m-d H:i:s",$v['creat_time']);
		        if($v['sell_time'] > 1){
		           $list[$k]['sell_times'] = date("Y-m-d",$v['sell_time']);
		           $list[$k]['sell_time_miao'] = date("Y-m-d H:i:s",$v['sell_time']);
		        }
		        if($v['sell_type'] >0){
		          $list[$k]['sell_types'] = $v['sell_type'] == 1?'自动卖出':'手动卖出';
		        }
		       
		      }
			$count = Db::name('trade_order')->where($map)->count('id');

			$data=['page'=>$page,'list'=>$list,'count'=>$count];
    
    
    	return msgreturn($data,'');
    
    
    
    
    
    }
  public function oldinfo(){
 	$id = request()->post("id");
 	$info = Db::name("trade_order")->where("id",$id)->where("user_id",$this->vip_info['id'])->find();
 	$info['sell_types'] = $info['sell_type'] == 1 ?'自动卖出':'手动卖出';
 	$info['deal_times'] = date("Y-m-d H:i:s",$info['deal_time']);
 	$info['sell_times'] = date("Y-m-d H:i:s",$info['sell_time']);
 	$info['chi_day'] = ceil(($info['sell_time']-$info['deal_time'])/86400);
 	$info['all_money'] = round($info['trush_number']*$info['trush_price'],2);
 	return msgreturn($info,'');
 }
	public function assets(){


	  //获取资金账户
      $vip_id = $this->vip_info['id'];
      $vip_info = Db::name("vip")->field("id,vip_money,vip_freeze")->where("id = {$vip_id}")->find();


      //获取持仓市值及盈亏


        //获取信用金列表
       $strategy_tj = module_config('trade.strategy_credit_rec');
       $strategy_list = explode("|",$strategy_tj);
       
       //获取信用金倍率
        $strategy_rate = module_config('trade.strategy_rate');
       $strategy_rate_list = explode("|",$strategy_rate);
      
       //获取递延费
       $strategy_renewal_fee = module_config('trade.strategy_renewal_fee');
      

       //获取综合服务费
       $strategy_fee = module_config('trade.strategy_fee');
       
       $data = [

       		'money'=>$vip_info,
         	'money_account'=>round($vip_info['vip_money']+$vip_info['vip_freeze'],2),
       		'gethas'=>gethascicang2($this->vip_info['id']),

       ];

       return msgreturn($data,'');

	}
	public function record(){

			$map['record_vip'] = $this->vip_info['id'];
			if($this->param['time']){


				$map['record_time'] =array('gt',strtotime("-".input('time')." month"));
			}

			$list = Db::name('vip_record')->where($map);
			$this->param['page'] = $this->param['page']?:1;
			if($this->param['page']){

				$list= $list->page($this->param['page'],15);

			}	

			$list = $list->order('record_time desc')->select();
			$record_type =config('MEONEY_TYPE');
			foreach ($list as $key => $value) {
				$list[$key]['type'] = $record_type[$value['type']];
				$list[$key]['record_time'] = date('Y-m-d',$value['record_time']);	
			}

			return msgreturn($list,'');






	}

	//获取可用余额
	public function getvipmoney(){
		$vip_money = Db::name("vip")->where("id",$this->vip_info['id'])->value("vip_money");
		return msgreturn($vip_money,'');
	}
	public function idcard_validate(){


		if($this->param['vip_realname'] && $this->param['vip_idcard']){

				$data['vip_realname'] = $this->param['vip_realname'];
				$data['vip_idcard']  = $this->param['vip_idcard'];
				
				$result = Db::name('vip')
				->where('id','=',$this->vip_info['id'])
				->update($data);
				if($result !==false){

					return msgreturn('认证成功','');


				}else{

					return msgreturn('','认证失败');

				}

		}


	}
	public function bank_validate(){


		if($this->param['phone_code'] && $this->param['bank_number'] && $this->param['bank_name']){



				$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>$this->vip_info['vip_phone']  ,'code'=>input('post.phone_code')]);
				if($res['status']!=2){

					return  json(['status'=>3,'message'=>$res['message']]);	

				}
				if(!$this->vip_info['vip_realname']||!$this->vip_info['vip_idcard']){

					return msgreturn('','请先实名认证');

				}
				$data['bank_name'] = $this->param['bank_name'];
				$data['bank_vip']  = $this->vip_info['id'];
				$data['bank_number'] = $this->param['bank_number'];
				$data['bank_time']   = time();
				$result = Db::name('vip_bank')
				->insert($data);




				if($result){

					return msgreturn('添加成功','');


				}else{

					return msgreturn('','添加失败');

				}

		}else{
			$data['vip_phone'] =$this->vip_info['vip_phone'];
			$data['bank'] =Db::name('vip_bank')->field('bank_number,bank_name')->where('bank_vip','=',$this->vip_info['id'])->find();
			return msgreturn($data,'');


		}


	}
	public function edit_password(){



		if(request()->isPost()){



			if(!Hash::check((string)input('post.password'),Db::name('vip')->where('id='.$this->vip_info['id'])->value('vip_password'))){

				return  json(['status'=>3,'message'=>'密码不正确']);		

			}

			$ret = Db::name('vip')->where('id',$this->vip_info['id'])->setField('vip_password',  Hash::make((string)input('post.newpass')));	


			if($ret){


				return  json(['status'=>2,'message'=>'修改成功']);		


			}else{
				return  json(['status'=>3,'message'=>'修改失败']);		
			}	






		}


	}
	public function user_trade(){


			$map['user_id'] = $this->vip_info['id'];
			$list = Db::name('trade_order')->where($map);

			$this->param['page'] = $this->param['page']?:1;
			if($this->param['page']){

				$list= $list->page($this->param['page'],10);

			}	
			$list= $list->select();
			$data['list'] = $list;
			$data['count'] = Db::name('trade_order')->where($map)->count('id');
			return msgreturn($data,'');



	}

	public function submit_invest(){

		if(request()->isPost()){


		$vip_id = $this->vip_info['id'];
        $parms = $this->param;
          //print_r($parms);exit;
        //股票开市时间验证
        $nowweek = date("w");
        if($nowweek == '6' || $nowweek=='0'){
        	return msgreturn('','周六周日为休市日不能交易');
        }
        $start_time = strtotime(date("Y-m-d"));
        //上午开盘时间9:30  
        $time930 = $start_time + 3600*9+1800;

        //上午休市时间 11:30
        $time1130 = $start_time+3600*11+1800;

        //下午开盘时间 13:00
        $time1300 = $start_time+3600*13;
        //下午休市时间 15:00
        $time1500 = $start_time+3600*15;

        $nowtime = time();

        if($nowtime < $time930 || $nowtime > $time1500){
        	return msgreturn('','暂未开盘');
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          return msgreturn('','暂未开盘');
        }
      
      //验证账户余额
        //获取综合服务费
        $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("vip")->where("id",$vip_id)->find();
      
        if($checkvip['vip_money'] < ($parms['money']+$strategy_fee)){
        	return msgreturn('','账户余额不足，请先充值');
        }

        if($parms['money']< 1 || $parms['strategy_rate'] < 1||$parms['winstops']== '0' || $parms['downstops'] == '0'){
        	return msgreturn('','参数有误，请重新提交');
        }
        if($parms['number'] < 100 || fmod($parms['number'],100) > 0){
        	return msgreturn('','委托数量有误，请刷新页面后重新选择提交');
        }
         //效验用户认购类型
        
        if($checkvip['buy_type'] < 1){
              $parms['buy_type'] = 0;
		        //计算委托数量
		        $EType = codefenxi($parms['stcode']);
		    
		      
		     $toparms = '{"req":"Trade_CommitOrder","rid":"1007","para":[ { "Code" : "'.$parms['stcode'].'", "Count" : '.$parms['number'].', "EType" : '.$EType.', "OType" : 1, "PType" : 1, "Price" : "'.$parms['price'].'" } ] }';
		      //error_log(print_r($toparms,1),3,'211.txt');
		    $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($toparms));
		     error_log(print_r($res,1),3,'2buysuccess.txt');
		       // $res = '{"event":"Trade_SendOrderEvent","rid":7,"sid":"21","cid":"0","data":[{"委托编号":"1051","返回信息":"","检查风险标志":"0","保留信息":"","(参数)操作数据":"","句柄":""}]}';
		      $info = json_decode($res,true);
		      if(isset($info['data'][0]['委托编号'])){
		          if($info['data'][0]['委托编号'] < 1){
		          	return msgreturn('','委托失败，请重新委托');
		          }

		      }

		      //var_dump($info['data'][0]['委托编号']);
		      //var_dump($info);die;
		       // print_r($info);exit;
		      if(isset($info['data']['ErrInfo']) || empty($res) || $res == false){
		      	return msgreturn('','委托失败');
		      }
		      $parms['bianhao'] = $info['data'][0]['委托编号'];
		    }else{
		    	 $parms['buy_type'] = 1;
		    }

      //var_dump($res);die;
      //file_put_contents("111.txt", $res);

        $res = UserModel::SetTradeOrder($parms,$vip_id);
           //$deal_number = explode('|',config('deal_number'));
          // $repay_money =round(($parms['money']+$parms['money']* $parms['strategy_rate'])*$deal_number[0]/10000+($parms['money']*$parms['strategy_rate'])*$deal_number[1]/10000,2);	
          $repay_money = ($parms['price']*$parms['number'])/10000*$strategy_fee;
        if($res){
            agent_repay($vip_id,$repay_money);
        	return msgreturn('委托成功','');
        }else{
        	return msgreturn('','委托失败');
        }

		}

	}
//获取线下充值方式
	public function payofftype(){
		$chargelist = Db::name('payoff_conf')->where("status",1)->select();
		foreach ($chargelist as $key => $value) {
			if($value['img'] > 0){
				$chargelist[$key]['img_url'] = get_file_path($value['img']);
			}
		}
		return msgreturn($chargelist,'');
	}
//线下充值
	public function payoff(){
		$params = request()->param();
		if($params['payofftype'] < 1 || empty($params['payofftype'])){
			return msgreturn('','请选择支付方式');
		}
		if($params['payoffmoney'] < 1){
			return msgreturn('','充值金额不能低于1元');
		}
		if($params['payoffinfo'] == ''){
			return msgreturn('','请填写充值备注信息');
		}
		$chargeconf = Db::name("payoff_conf")->where("id",$params['payofftype'])->find();
		$data['recharge_amount'] = $params['payoffmoney'];
		$data['recharge_status'] = 0;
		$data['recharge_vip'] = $this->vip_info['id'];
		$data['recharge_type'] = 2;
		$data['recharge_info'] = $params['payoffinfo'];
		$data['recharge_title'] = $chargeconf['title'];
		$data['recharge_bankname'] = $chargeconf['bankname'];
		$data['recharge_number'] = $chargeconf['number'];
		$data['recharge_time'] = time();
		$res = Db::name("vip_recharge")->insert($data);
		if($res){
			return msgreturn('提交申请成功','');
		}else{
			return msgreturn('','提交申请失败');	
		}


	}
//充值记录
	public function rechargelog(){

		$map['recharge_vip']= $this->vip_info['id'];
		
		if(input('time')){


			$map['recharge_time'] =array('gt',date("Y-m-d",strtotime("-".input('time')." month")));
		}
		
		$status_name = array(0=>'待审核',1=>'成功',2=>'失败',3=>'成功');
		$recharge_type = array(1=>'线上支付',2=>'线下支付');
		
		$list = Db::name('vip_recharge')->where($map)->order("id desc")->paginate(10)->each(function($item,$key)use($status_name,$recharge_type){
			$item['status_name'] = $status_name[$item['recharge_status']];
			$item['type_name'] = $recharge_type[$item['recharge_type']];
			$item['recharge_time'] = date("Y-m-d H:i",$item['recharge_time']);
			return $item;
		});
	return msgreturn($list,'');

	}
//提现记录 
	public function withdrawlog(){

		$map['withdraw_vip']= $this->vip_info['id'];
		
		if(input('time')){


			$map['withdraw_time'] =array('gt',date("Y-m-d",strtotime("-".input('time')." month")));
		}
		
		$status_name = array(0=>'待审核',1=>'成功',2=>'失败');
		
		
		$list = Db::name('vip_withdraw')->where($map)->order("id desc")->paginate(10)->each(function($item,$key)use($status_name){
			$item['status_name'] = $status_name[$item['withdraw_status']];
			$item['withdraw_time'] = date("Y-m-d H:i",$item['withdraw_time']);
			return $item;
		});

 		return msgreturn($list,'');


	}

///////站内信列表////
	public function innermsglist(){
		$list = Db::name("vip_innermsg")->where("vip_id",$this->vip_info['id'])->order("id desc")->paginate(10)->toArray()['data'];
		foreach ($list as $key => $value) {
			$list[$key]['send_times'] = date("Y-m-d H:i",$value['send_time']);
		}
		return msgreturn($list,'');
	}

///////修改支付密码///////////
	public function edit_paypassword(){

				if(request()->isPost()){



					if(!Hash::check((string)input('post.password'),Db::name('vip')->where('id='.$this->uid)->value('vip_paypassword'))){
						return msgreturn('','支付密码不正确');		

					}

					$ret = Db::name('vip')->where('id',$this->uid)->setField('vip_paypassword',  Hash::make((string)input('post.newpass')));	


					if($ret){

						return msgreturn('修改成功','');
					}else{
						return msgreturn('','修改失败');	
					}	

				}

			}
//////提现/////////
	public function submitwithdraw(){

		if(Request::instance()->isPost()){

				

			//$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' =>Db::name('vip')->where('id='.$this->uid)->value('vip_phone')  ,'code'=>input('post.vip_phone')]);
			/*if($res['status']!=2){

				return  json(['status'=>3,'message'=>$res['message']]);	

			}*/
			if(!Hash::check((string)input('post.vip_paypassword'),Db::name('vip')->where('id='.$this->vip_info['id'])->value('vip_paypassword'))){
				return msgreturn('','支付密码不正确');	

			}
			$bankinfo = Db::name('vip_bank')->where('bank_vip='.$this->vip_info['id'])->find();
			if(!$bankinfo){
				return msgreturn('','没有你的银行卡信息');		
			}
			$vip_money = Db::name("vip")->where("id",$this->vip_info['id'])->value("vip_money");
			if($vip_money < input('post.withdraw_money')){
				return msgreturn('','提现金额不得大于可用余额');	
			}
			$data['withdraw_vip'] = $this->vip_info['id'];
			$data['withdraw_amount'] = input('post.withdraw_money');
			$data['withdraw_time'] = time();
			$data['withdraw_card'] =  $bankinfo['bank_number'];
			$data['withdraw_realname'] =  Db::name('vip')->where('id='.$this->vip_info['id'])->value('vip_realname');
			$data['withdraw_bank'] =  $bankinfo['bank_name'];
			Db::startTrans();
			try{
				Db::name('vip_withdraw')->insertGetId($data);
				money_log($data['withdraw_amount'],$data['withdraw_vip'],2,'提现扣除金额');
				Db::commit();
				return msgreturn('提现申请成功','');
			}catch (\Exception $e) {


				 Db::rollback();
				 return msgreturn('','提现申请失败');	
			}

		}


	}

////////订阅列表////
	public function ace(){
		//订阅牛人
		$list['rsslist'] = get_rss_list($this->vip_info['id']);
		//推荐持仓
		$list['bestlist'] = tradebestlist();
		//牛人排行
		$list['niulist'] = get_niu_list($this->vip_info['id']);

		return msgreturn($list,'');

	}
//设置订阅
	 public function setRss(){
    	$other_id = request()->param('id');
    	if($other_id < 1){
    		return false;
    	}
    	$res = setRss_info($other_id,$this->vip_info['id']);
    	
    	return msgreturn($res,'');
    }


/////////////////////////////////////模拟大赛/////////////////////////
    public function match_index(){
      $vip_id = $this->vip_info['id'];
      $match_info = Db::name("match_info")->where("vip_id",$vip_id)->find();
      $match_info['vip_name'] = Db::name("vip")->where("id",$vip_id)->value("vip_name");
      $match_info['head_img'] = getheadimg($vip_id); 
      $dede['match_info'] = $match_info;
      //昨日收益
      $yest_start = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
      $yest_end = strtotime(date('Y-m-d'.'00:00:00',time()));
      $map['creat_time'] =array("between",[$yest_start,$yest_end]);
      $map['user_id'] = $vip_id;
      $map['status'] = 2;
      $yest_porify = Db::name("match_order")->where($map)->sum('repay_profits');

      $dede['yest_porify'] = $yest_porify;
      //总收益排行
      $dede['zsypaihang'] = getyestprofits(1);
      //日收益排行
      $dede['rsypaihang'] = getyestprofits(2);
      //周收益排行
      $dede['wsypaihang'] = getyestprofits(3);
      //月收益排行
      $dede['ysypaihang'] = getyestprofits(4);

     return msgreturn($dede,'');
    }


 //个人主页
    public function userhome(){
      $id = request()->param('id');
      $id = $id < 1?$this->vip_info['id']:$id;
      file_put_contents("userid.txt",print_r($id,true));

      $info = Db::name("vip")->field("id,vip_name,last_login_time")->where("id",$id)->find();
	  $info['hotnum'] = Db::name("vip_rss")->where("other_id",$id)->count("id");
	  $count = Db::name("vip_rss")->where("vip_id",$this->vip_info['id'])->where("other_id",$id)->count('id');
	  $info['rss_status'] = $count > 0?1:0;
	  $info['head_img'] = getheadimg($id);
	  $info['vip_name'] = hidecard($info['vip_name'],2);
	  $info['last_times'] = second2string($info['last_login_time'],1,1);
      $dete['info'] = $info;
      //实盘
      $shipan = spgetoneprofits($id,1);
      $dete['shipan'] = $shipan;
      //实盘日
      $shipanr = spgetoneprofits($id,2);
       $dete['shipanr'] = $shipanr;

      //实盘周
      $shipanw = spgetoneprofits($id,2);
       $dete['shipanw'] = $shipanw;

      //实盘月
      $shipany = spgetoneprofits($id,2);
       $dete['shipany'] = $shipany;

      //大赛
      $dasai = getoneprofits($id,1);
       $dete['dasai'] = $dasai;
      //大赛日
      $dasair = getoneprofits($id,2);
       $dete['dasair'] = $dasair;

      //大赛周
      $dasaiw = getoneprofits($id,2);
       $dete['dasaiw'] = $dasaiw;

      //大赛月
      $dasaiy = getoneprofits($id,2);
      $dete['dasaiy'] = $dasaiy;

      $list = GetAllOrders($id);
      $dete['list'] = $list;

      return msgreturn($dete,'');
    }


   //加入自选
    public function add_gprss(){
      $parms = request()->param();
      if($parms['gupiao_code'] == ''|| $parms['gupiao_name'] == ''){
      	 return msgreturn('','参数有误，请重新提交');
      }
      $parms['vip_id'] = $this->vip_info['id'];
      $check = Db::name("gupiao_rss")->where("gupiao_code",$parms['gupiao_code'])->where("vip_id",$parms['vip_id'])->select();
     
      $parms['add_time'] = time();
      if(count($check)>0){
        $res = Db::name("gupiao_rss")->where("id",$check[0]['id'])->delete();
        if($res){
        	 return msgreturn('','删除自选成功');
        }else{
        	 return msgreturn('','删除自选失败');
        }
      }else{
      	 if(count($check) > 10){
      	 	 return msgreturn('','最多加入10个自选');	
      	}
        $res = Db::name("gupiao_rss")->insert($parms);
        if($res){
        	return msgreturn('添加自选成功','');
        }else{
        	return msgreturn('','添加自选失败');
        }
      }
      
      
    }


    //获取是否自选
    public function getrss_status(){
    	$parms = request()->param();
    	 if($parms['gupiao_code'] == ''){
      	 	return msgreturn('','参数有误，请重新提交');
     	 }
     	  $parms['vip_id'] = $this->vip_info['id'];
    	$check = Db::name("gupiao_rss")->where("gupiao_code",$parms['gupiao_code'])->where("vip_id",$parms['vip_id'])->count("id");
    	if($check > 0){
    		return msgreturn('取消自选','');
    	}else{
    		return msgreturn('加入自选','');
    	}
    }

    public function getgupiaorss_list(){
    	$list = Db::name("gupiao_rss")->where("vip_id",$this->vip_info['id'])->select();
    	foreach ($list as $key => $value) {
    		$hqinfo = get_code_info($value['gupiao_code']);
    		if(isset($hqinfo[1][3])){
    			$list[$key]['price'] = $hqinfo[1][3];
    			if($hqinfo[1][3] > $hqinfo[1][5]){
    				$list[$key]['zdstatus'] = 1;//涨
    				$list[$key]['zdrate'] = round(($hqinfo[1][3]-$hqinfo[1][5])/$hqinfo[1][5]*100,2);
    			}else{
    				$list[$key]['zdstatus'] = 0;//跌
    				$list[$key]['zdrate'] = round(($hqinfo[1][5]-$hqinfo[1][3])/$hqinfo[1][5]*100,2);
    			}
    			
    		}else{
    			$list[$key]['price'] = 0;
    		}
    		
    	}
    	return msgreturn($list,'');
    }
  //修改止盈止损
   //设置单个持仓止盈止损
    public function setstopstatus(){
      $id = intval($_GET['id']);
      $type = intval($_GET['type']);
      $val = floatval($_GET['val']);
      $credit = intval($_GET['credit'])>0?intval($_GET['credit']):0;
      if($id < 1 || $type > 2 || $val <= 0){
        return json(['status'=>0,'message'=>'参数有误，请重新提交']);
      }
      $info = Db::name("trade_order")->where("id",$id)->find();
      if($type == 1){ //止盈
         if($val <= $info['now_price']){
              return json(['status'=>0,'message'=>'止盈价格不得低于当前价']);
          }
          if($val == $info['stop_win']){
              return json(['status'=>0,'message'=>'新止盈价格不得之前止盈价格相同']);
          }
         $res = Db::name("trade_order")->where("id",$id)->setField("stop_win",$val);
      }elseif($type == 2){ //止损
          if($val <= 0){
              return json(['status'=>0,'message'=>'止损价格不得小于等于0']);
          }
          if($val >= $info['now_price']){
              return json(['status'=>0,'message'=>'止损价格不得高于等于当前价']);
          }
          $downstop = module_config('trade.downstop');
          //最低止损价格
          $mindownstop_price = floatval((($info['trush_price']*$info['trush_number'])-($info['credit_money']*$downstop/100))/$info['trush_number']);
          if($val < $mindownstop_price){
              //应追加的最低信用金
              $yingdownstop_price = intval(($info['trush_price']*$info['trush_number']-$val)/$downstop*100);
              if($credit < $yingdownstop_price){
                 return json(['status'=>0,'message'=>'如需修改为该止损价格，您最少应该追加信用金{$yingdownstop_price}元']);
              }
          }
          $res = InvestModel::SetStopdownPrice($id,$val,$credit);

      }else{
        return json(['status'=>0,'message'=>'参数有误，请重新提交']);
      }
      if($res){
        return json(['status'=>1,'message'=>'设置成功']);
      }else{
        return json(['status'=>0,'message'=>'设置失败，请重试']);
      }

    }
  //卖出策略
      public function tosell(){

         $id = intval($_GET['id']);
        if($id < 1){
           return json(['status'=>0,'message'=>'非法数据，请刷新后重试']);
        }
        //股票开市时间验证
        $nowweek = date("w");
        if($nowweek == '6' || $nowweek=='0'){
          return json(['status'=>0,'message'=>'周六周日为休市日不能交易']);
        }
        $start_time = strtotime(date("Y-m-d"));
        //上午开盘时间9:30  
        $time930 = $start_time + 3600*9+1800;

        //上午休市时间 11:30
        $time1130 = $start_time+3600*11+1800;

        //下午开盘时间 13:00
        $time1300 = $start_time+3600*13;
        //下午休市时间 15:00
        $time1500 = $start_time+3600*15;

        $nowtime = time();

      /*  if($nowtime < $time930 || $nowtime > $time1500){

           return json(['status'=>0,'message'=>'暂未开盘']);
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          
           return json(['status'=>0,'message'=>'暂未开盘']);
        }*/
         $res = InvestModel::sellorder($id);
        if($res){
            return json(['status'=>1,'message'=>'卖出委托成功']);
        }else{
            return json(['status'=>0,'message'=>'卖出委托失败']);
        }


    }
   public function change_defer(){
   
   			if(request()->isPost()){
            		$data= [
                   	'id'=>input('post.id'),
                     'user_id'=>$this->vip_info['id'],
                      'defer_status'=>input('post.status')  
                    ];
            	$res = Db::name('trade_order')->update($data);
              if($res!== false){
              	return msgreturn('更改状态成功','');         
              }else{
              	return msgreturn('','更改状态失败');
              }
            
            }

   }
  
  	public function payonline(){
		$params = request()->param();
		/*if($params['payofftype'] =='' || empty($params['payofftype'])){
			return msgreturn('','请选择支付方式');
		}*/
		if($params['money'] < 1){
			return msgreturn('','充值金额不能低于1元');
		}
	     	if($params['payofftype'] == 'fuyouwap'){
        
        	/************************妇友支付******************/
          	/*$key ='gcpguo0qso5eflnm9lb9m30j5m7e5sfx';
          	$sumitUrl="https://mpay.fuioupay.com:16128/h5pay/payAction.pay";
          	//$sumitUrl = "http://www-1.fuioupay.com:18670/mobilepay/h5pay/payAction.pay";
          	$version = '2.0';
          	$mchntcd='0001000F2254929';
          	$type ='10';
          	$logotp='0';
          	$mchntorderid=time().rand(1000,9999);
          	$userid = $this->vip_info['id'];
          	$amt = $params['money']*100;
          	$bankcard = $params['bankcard'];
          	$vip_info=Db::name('vip')->field('vip_idcard,vip_realname')->where('id',$userid)->find();
          //print_r($vip_info);
          	if(empty($vip_info['vip_idcard'])||empty($vip_info['vip_realname'])){
            
            	$this->error('您还没有实名认证,请先认证');
            }
          	$name=$vip_info['vip_realname'];
          	$idno = $vip_info['vip_idcard'];
          	$idtype ='0'; 
          	$backurl = 'http://'.$_SERVER['HTTP_HOST'].'/index/notify/fuyounotify';
          	$homeurl = 'http://'.$_SERVER['HTTP_HOST'].'/wap';
          	$reurl = 'http://'.$_SERVER['HTTP_HOST'].'/wap';
          	$rem1 = '';
          	$rem2='';
          	$rem3="";
          	$signtp ='md5';
          	$sign = $type."|".$version."|".$mchntcd."|".$mchntorderid ."|".$userid."|".$amt."|".$bankcard."|".$backurl."|".
   		    $name."|".$idno."|".$idtype."|".$logotp."|".$homeurl."|".$reurl."|".$key;
          	$sign = str_replace(' ', '', $sign); 
          	$fm = "<ORDER>"
   		  	."<VERSION>".$version."</VERSION>"
			."<LOGOTP>".$logotp."</LOGOTP>"
			."<MCHNTCD>".$mchntcd."</MCHNTCD> "
			."<TYPE>".$type."</TYPE>" 
			."<MCHNTORDERID>".$mchntorderid."</MCHNTORDERID>" 
			."<USERID>".$userid."</USERID>" 
			."<AMT>".$amt."</AMT>" 
			."<BANKCARD>".$bankcard."</BANKCARD>" 
			."<NAME>".$name."</NAME>" 
			."<IDTYPE>".$idtype."</IDTYPE>" 
			."<IDNO>".$idno."</IDNO>" 
			."<BACKURL>".$backurl."</BACKURL>" 
			."<HOMEURL>".$homeurl."</HOMEURL>" 
			."<REURL>".$reurl."</REURL>" 
			."<REM1>".$rem1."</REM1>" 
			."<REM2>".$rem2."</REM2>"
			."<REM3>".$rem3."</REM3>" 
			."<SIGNTP>".$signtp."</SIGNTP>" 
			."<SIGN>".md5($sign)."</SIGN>" 
			."</ORDER>"; 
          	$data=[
              	'ENCTP'=>'1',
            	'VERSION'=>$version,
              	'MCHNTCD'=>$mchntcd,
              	'FM'=>$this->encryptForDES($fm,$key),
 
            ];
          	$this->createnid($params['money'],$mchntorderid);
            $this->create($sumitUrl,$data);*/
        	//print_r($data);
              //start   ***********
              	$userid = $this->vip_info['id'];	
              $vip_info=Db::name('vip')->field('vip_idcard,vip_realname')->where('id',$userid)->find();
          //print_r($vip_info);
          	if(empty($vip_info['vip_idcard'])||empty($vip_info['vip_realname'])){
            
            	$this->error('您还没有实名认证,请先认证');
            }
               $sbdata = [
        	'partner'=>'321129',
        	'channelid'=>'29',
          	'orderno'=> time().rand(1000,9999),
         	 'amount'=>$params['money'],
          	'notifyurl'=>"http://".$_SERVER['HTTP_HOST']."/index/notify/td28_notify",
          	'return_url'=>"http://".$_SERVER['HTTP_HOST']."/wap",
        	'card_no'=>$params['bankcard'],
          	'cardholder_name'=>$vip_info['vip_realname'],
          	'cert_no'=> $vip_info['vip_idcard'],
        ];
        $signstring ='';
        ksort($sbdata);
      	foreach($sbdata as $key=>$value){
			if($value!=''){
			$signstring.=$key.'='.$value.'&';	
              }

        }
          $sbdata['sign'] = strtoupper(md5($signstring.'key=gmGKwsetBxm8iXjP2pIX8H5Yv1ZzSrra'));
        $this->createnid($params['money'],$sbdata['orderno']);
      	$this->create('http://www.xcwhwh.cn:39110/api/pay/order_pay',$sbdata);
        }
           /***支付宝***/
      if($params['payofftype'] == 'alipaywap'){
      	$appid = '3114081111';
        $appkey='92e8e5f9d7a15777832c5f84b3fbc6d0';
        	$yundata = array(
                     "appid"  => $appid,
                     "data"   =>time().rand(1000,9999),//网站订单号/或者账号
                     "money"  => number_format($params['money'],2,".",""),//注意金额一定要格式化否则token会出现错误
                     "type"   => (int)1,
                     "uip"    => $this->getIp(),
                  );
        	$token = array(
                    "appid"  =>  $appid,//APPID号码
                    "data"   =>  $yundata["data"],//数据单号
                    "money"  =>  $yundata["money"],//金额
                    "type"   =>  $yundata["type"],//类别
                    "uip"    =>  $yundata['uip'],//客户IP
                    "appkey" => $appkey//appkey密匙
                  );
        $token = md5($this->urlparams($token));
		$postdata = $this->urlparams($yundata).'&token='.$token;
        //构建请求二维码

		 $order_data = base64_encode($yundata["data"].','.$yundata["money"]);//将数据进行base64编码
   		  $qrcode = 'http://'.$_SERVER['HTTP_HOST'].'/index/Alipay?data='.$order_data.'&uid='.$this->vip_info['id'];//本地自动生成二维码地址
  		 $sdata = array('state'=>1,'qrcode'=>$qrcode,'youorder'=>$yundata["data"],'data'=>$yundata["data"],'money'=>$yundata["money"],'times'=>time() + 300,'orderstatus'=>0,'text'=>10089); //本地生成二维码可手动伪造JSON数据
         $state = $sdata["state"];//状态 1 ok   0有错误

        if(!$state){
            exit('异常'.$sdata["text"]);
        }

        $qrcode = $sdata["qrcode"];//二维码

        $times =  $sdata["times"] - time(); //有效时间减去当前时间 保留一分钟减去60秒

        $moneys = $sdata["money"];//实际付款金额

        $orderstatus =$sdata["orderstatus"];//付款状态 1ok  0等待付款

        $data =$sdata["data"];//传递的订单号

        $order =$sdata["order"];//云端分配的唯一订单号 通过这个订单号查询状态

        //

		$logo = '/static/home/js/template/Image/zfb.png';
		$title = '支付宝';	
		$text =  '支付宝扫一扫付款（手机上可以直接启动APP，或者截图相册识别）';
		$tishi = '<div style="position:relative;width:300px;height:341px;margin:0 auto;border:1px solid #e4e3e3"><img src="/static/home/js/template/Image/zfbbg.png" alt="" /><div style="position:absolute;width:100px;height:100px;z-indent:2;left:35;top:210;font-size:48px;color:#F00">'.$moneys.'</div></div>';
      //html页面
		$this->createnid($moneys,$data);
        return $this->fetch('index@index/Alipay',['title'=>$title,'logo'=>$logo,'moneys'=>$moneys,'text'=>$text,'data'=>$data,'times'=>$times,'qrcode'=>$qrcode,'order'=>$order,'tishi'=>$tishi]); 	
      }
		//var_dump($params);
	}

	private function createnid($money,$order){
		$data['recharge_amount'] = $money;
		$data['recharge_status'] = 0;
		$data['recharge_vip'] = $this->vip_info['id'];
		$data['recharge_type'] = 1;
		$data['recharge_order'] = $order;
		$data['recharge_time'] = time();
		$res = Db::name("vip_recharge")->insert($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}


	private function getsign($data,$newkey,$type='ksort',$nul='1'){
		switch ($type) {
			case 'ksort':
				ksort($data);
				$pinjie = '';
				foreach ($data as $key => $value) {
				    if(!empty($value)&&$value !==''){
				         $pinjie .= $key."=".$value."&";
				    }
				   
				}
				$pinjie = substr($pinjie,0,strlen($pinjie)-1).$newkey;
				//var_dump($pinjie);
				$sign = strtoupper(md5($pinjie));
				break;
		
		}
		return $sign;
	}
	private function submitdata($url,$data){
		$options = array(
		    'http' => array(
		        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method' => 'POST',
		        'content' => http_build_query($data)
		    ),
		   /* "ssl"=>array(
		                "verify_peer"=>false,
		                "verify_peer_name"=>false,
		            )*/
		);
		//var_dump($options);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
  /***************************************************************/
    
  	    public static  function encryptForDES($input,$key)   
    {         
       $size = mcrypt_get_block_size('des','ecb');  
       $input = self::pkcs5_pad($input, $size);  
       $td = mcrypt_module_open('des', '', 'ecb', '');  
       $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);  
       @mcrypt_generic_init($td, $key, $iv);  
       $data = mcrypt_generic($td, $input);  
       mcrypt_generic_deinit($td);  
       mcrypt_module_close($td);  
       $data = base64_encode($data);  
       return $data;  
    }   
    
             
    public static  function pkcs5_pad ($text, $blocksize)   
    {         
       $pad = $blocksize - (strlen($text) % $blocksize);  
       return $text . str_repeat(chr($pad), $pad);  
    } 
        
    public static  function pkcs5_unpad($text)   
    {         
       $pad = ord($text{strlen($text)-1});  
       if ($pad > strlen($text))  
       {  
           return false;  
       }  
       if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)  
       {  
          return false;  
       }  
       return substr($text, 0, -1 * $pad);  
    }
  	private function create($submitUrl,$data){
		$inputstr = "";
		foreach($data as $key=>$v){
			$inputstr .= '
		<input type="hidden"  id="'.$key.'" name="'.$key.'" value=\''.$v.'\'"/>
		';
		}
		
		$form = '
		<form action="'.$submitUrl.'" name="pay" id="pay" method="POST">
';
		$form.=	$inputstr;
		$form.=	'
</form>
		';
		
		$html = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>请不要关闭页面,支付跳转中.....</title>
        </head>
<body>
        ';
        $html.=	$form;
        $html.=	'
        <script type="text/javascript">
			document.getElementById("pay").submit();
		</script>
        ';
        $html.= '
        </body>
</html>
		';
				 
		//Mheader('utf-8');
		echo $html;
		exit;
	}
    //获取客户端IP地址
 public function getIp()
  { //取IP函数
      static $realip;
      if (isset($_SERVER)) {
          if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
              $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
          } else {
              $realip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
          }
      } else {
          if (getenv('HTTP_X_FORWARDED_FOR')) {
              $realip = getenv('HTTP_X_FORWARDED_FOR');
          } else {
              $realip = getenv('HTTP_CLIENT_IP') ? getenv('HTTP_CLIENT_IP') : getenv('REMOTE_ADDR');
          }
      }
      $realip=explode(",",$realip);

      return $realip[0];
  }
   //数组拼接为url参数形式
public function urlparams($params){
    $sign = '';
    foreach ($params AS $key => $val) {
        if ($val == '') continue;
        if ($key != 'sign') {
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val"; //拼接为url参数形式
        }
    }
    return $sign;
}

}