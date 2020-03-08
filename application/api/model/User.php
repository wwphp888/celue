<?php

namespace app\api\model;

use think\Model as ThinkModel;
use think\Db;
/**
 * 股票交易模型 
 * @package app\Invest\model
 */
class User extends ThinkModel
{
	  public static function SetTradeOrder($parms,$vip_id){
           //获取递延费
       $strategy_renewal_fee = module_config('trade.strategy_renewal_fee');
       //获取综合服务费
       $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("vip")->where("id",$vip_id)->find();

        if($checkvip['vip_money'] < $parms['money']+$strategy_fee){
            return false;
        }
       
        Db::startTrans();
        try{
            $data['order_no'] = "OR".time().rand(100,999); //订单号
            $data['user_id'] = $checkvip['id']; //用户名
            $data['gupiao_name'] = $parms['stname']; //股票名称
            $data['gupiao_code'] = $parms['stcode']; //股票代码
            $data['now_price'] = $parms['price'];
            
            $data['credit_money'] = $parms['money']; //信用金
            $data['credit_rate'] = $parms['strategy_rate']; //信用倍率
            $data['stop_win'] = $parms['winstops']; //止盈价
            $data['stop_down'] = $parms['downstops']; //止损价
            $data['trush_price'] = $parms['price']; //委托价格
            $data['trush_number'] = $parms['number']; //委托数量
            $data['trush_no'] = $parms['bianhao']; //委托编号（由接口返回）

            //应缴综合服务费
            $all_strategy_fee = ($parms['price']*$parms['number'])/10000*$strategy_fee;
            //  $all_strategy_fee =round(($data['credit_money']+$data['credit_money']* $data['credit_rate'])*3/1000+($data['credit_money']*$data['credit_rate'])*30/10000,2);
            $data['service_money'] = $strategy_fee; //综合服务费
            $data['pay_service_money'] = $all_strategy_fee; //应缴服务费
            $data['defer_money'] = $strategy_renewal_fee; //递延费
            $data['pay_defer_money'] = 0; //应缴递延费
            if($parms['autostatus'] == 1){
                $data['defer_status'] = 1;
            }else{
                $data['defer_status'] = 0;
            }
            $data['creat_time'] = time();
            $data['buy_type'] = $parms['buy_type'];
            if($parms['buy_type'] < 1){
                $data['status'] = 1; // 1为委托成功交易中
            }else{
                $data['status'] = 2; //虚拟盘直接成交
                $data['trush_no'] ='';
                $data['deal_number'] = $data['trush_number'];
                $data['deal_time'] = time();

            }

            $trade_id = Db::name("trade_order")->insertGetId($data);
        //  dump($trade_id);
            settrade_log($trade_id,"建仓成功");
            money_log(-$data['credit_money'],$data['user_id'],5,"对".$data['gupiao_name']."进行认购扣除信用金".$data['credit_money']);
            money_log(-$data['pay_service_money'],$data['user_id'],6,"对".$data['gupiao_name']."进行认购扣除综合服务费".$data['pay_service_money']);
            
            Db::commit(); 
            return true;
        } catch (\Exception $e) {
         // dump($e);
            // 回滚事务
            Db::rollback();
            
            return false;
        }



    }


}