<?php

namespace app\vip\home;

use app\vip\home\Index;
use think\Db;
use think\Request;


class Trade extends Index{


	public function index(){
		if(request()->isPost()){

			$map = array();
			$map['user_id'] = $this->uid;
			if(input('post.type')=='trade'){

				$map['status']= 4;	

			}
			if(input('post.time')!=''){
				$timestr = explode("|",input('post.time'));
				$mstr1 = strtotime($timestr[0]);
				$mstr2 = strtotime($timestr[1]);
				$map['deal_time']= array('between',"{$mstr1},{$mstr2}");	

			}
			
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

			return json(['page'=>$page,'list'=>$list,'count'=>$count]);

			}else{

				return 	$this->fetch();
			}
		 	
		}

 public function oldinfo(){
 	$id = request()->post("id");
 	$info = Db::name("trade_order")->where("id",$id)->find();
 	$info['sell_types'] = $info['sell_type'] == 1 ?'自动卖出':'手动卖出';
 	$info['deal_times'] = date("Y-m-d H:i:s",$info['deal_time']);
 	$info['sell_times'] = date("Y-m-d H:i:s",$info['sell_time']);
 	$info['chi_day'] = ceil(($info['sell_time']-$info['deal_time'])/86400);
 	$info['all_money'] = round($info['trush_number']*$info['trush_price'],2);
 	return $info;
 }




}