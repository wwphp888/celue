<?php

namespace app\index\model;

use think\Model as ThinkModel;
use think\Db;
/**
 * 股票交易模型
 * @package app\Invest\model
 */
class Invest extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__TRADE_ORDER__';

     //股票列表
    public static function GetGupiaolist($map)
    {
        
        $order = "id desc";
        $map['status'] = 1;
        $list = Db::name('gupiao_list')
            ->field('id,title,code,pinyin')
            ->where($map)
            ->order($order)
            ->limit(10)
            ->select();
        return $list;
    }

    public static function SetTradeOrder($parms){
         //获取递延费
       $strategy_renewal_fee = module_config('trade.strategy_renewal_fee');
       //获取综合服务费
       $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("vip")->where("id",$_SESSION['vip_id'])->find();

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
          	//	$all_strategy_fee =round(($data['credit_money']+$data['credit_money']* $data['credit_rate'])*3/1000+($data['credit_money']*$data['credit_rate'])*30/10000,2);
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
            settrade_log($trade_id,"建仓成功");
            money_log(-$data['credit_money'],$data['user_id'],5,"对".$data['gupiao_name']."进行认购扣除信用金".$data['credit_money']);
            money_log(-$data['pay_service_money'],$data['user_id'],6,"对".$data['gupiao_name']."进行认购扣除综合服务费".$data['pay_service_money']);
            
            Db::commit(); 
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            
            return false;
        }


    }

    //调整止损
public static function SetStopdownPrice($id,$val,$credit){
    $info = Db::name("trade_order")->where("id",$id)->find();
    $vip_info = Db::name("vip")->where("id",$info['user_id'])->find();
    if($credit >0){
        Db::startTrans();
        try{
            $yingcredit = $info['credit_money']+$credit;
             Db::name("trade_order")->where("id",$id)->update(['credit_money'=>$yingcredit,"stop_down"=>$val]);
             $log['record_vip'] = $info['user_id'];
             $log['type'] = 11; //实盘保证金追加
             $log['record_affect'] = $credit;
             $log['record_money'] = $vip_info['vip_money']-$log['record_affect'];
             $log['record_info'] = "对".$info['gupiao_name']."进行追加保证金";
             $log['record_time'] = time();
             Db::name("vip_record")->insert($log);

             Db::name('vip')->where('id', $info['user_id'])->setDec('vip_money',$log['record_affect']);
            Db::commit(); 
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }

    }else{
         $res = Db::name("trade_order")->where("id",$id)->setField("stop_down",$val);
         if($res){
            return true;
         }else{
            return false;
         }
    }

    

}
 

 /*public static function sellorder($id,$selltype=2){
        if($id < 1){
             return false;
        }
        $trade_info = Db::name("trade_order")->where("id",$id)->find();
        if($trade_info['status'] != '2'){
             return false;
        }
         $EType = codefenxi($trade_info['gupiao_code']);
   		$nowinfos = get_code_info($trade_info['gupiao_code'])[1];
   //print_r($nowinfos);exit;
         if(!isset($nowinfos[3])){
             return false;
         }
        
    
          $toparms = '{"req":"Trade_CommitOrder","rid":"1008","para":[ { "Code" : "'.$trade_info['gupiao_code'].'", "Count" : '.$trade_info['deal_number'].', "EType" : '.$EType.', "OType" : 2, "PType" : 1, "Price" : "'.$nowinfos[3].'" } ] }';

          $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($toparms));
        
          $info = json_decode($res,true);
   //print_r($info);exit;
          if(isset($info['data'][0]['委托编号'])){
              if($info['data'][0]['委托编号'] < 1){
                 return false;
              }

          }
           if(isset($info['data']['ErrInfo']) || empty($res) || $res == false){
                return false;
              }
              
         $sdata['sell_trush_no'] = $info['data'][0]['委托编号'];
         $sdata['sell_type'] = $selltype; //后台卖出显示为1自动卖出 2shoudong 
         $sdata['sell_price'] = $nowinfos[3];
         $sdata['sell_time'] = time();
         $sdata['sell_number'] = $trade_info['deal_number'];
         $sdata['status'] = 3; //卖出委托中
         $results = Db::name("trade_order")->where("id",$id)->update($sdata);
         if($results){
             settrade_log($id,"卖出委托成功");
            //给代理商返佣
            $ying_repay = $orderinfo['pay_service_money']+$orderinfo['pay_defer_money'];
             agent_repay($orderinfo['user_id'],$ying_repay);
             return true;
           // $this->success("卖出委托成功");
         }else{
             return false;
           // $this->error("卖出委托失败");
         }

 }*/
  public static function sellorder($id,$selltype=2){
    // return json(['status'=>1,'message'=>'11111']);
    //exit;
        if($id < 1){
             return false;
        }
        $trade_info = Db::name("trade_order")->where("id",$id)->find();
    		//print_r(date('m-d',time()).date('m-d',$trade_info['creat_time']));exit;
    	/*if(date('m-d',time())==date('m-d',$trade_info['creat_time'])){
        
        	return false;
        }*/
        $EType = codefenxi($trade_info['gupiao_code']);
        $nowinfos = get_code_info($trade_info['gupiao_code'])[1];
         if(!isset($nowinfos[3])){
             return false;
         }
   // print_r($trade_info);exit;
        if($trade_info['buy_type'] == 0){
             $toparms = '{"req":"Trade_CommitOrder","rid":"1008","para":[ { "Code" : "'.$trade_info['gupiao_code'].'", "Count" : '.$trade_info['deal_number'].', "EType" : '.$EType.', "OType" : 2, "PType" : 1, "Price" : "'.$nowinfos[3].'" } ] }';

              $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($toparms));
            
              $info = json_decode($res,true);
              if(isset($info['data'][0]['委托编号'])){
                  if($info['data'][0]['委托编号'] < 1){
                     return false;
                  }

              }
               if(isset($info['data']['ErrInfo']) || empty($res) || $res == false){
                    return false;
                  }
             $sdata['sell_trush_no'] = $info['data'][0]['委托编号'];
          	$sdata['status'] = 3;
        }else{
            $sdata['sell_trush_no'] = '';
          	$sdata['status'] = 4;
        	   
        }
   		if(	$sdata['status'] == 4){
        	
        	$prifits=($nowinfos[3]-$trade_info['trush_price'])*$trade_info['deal_number'];
            if($prifits > 0){
              $sdata['repay_creat_money'] = $trade_info['credit_money'];
              $sdata['repay_profits'] = $prifits;
            }else{
              $sdata['repay_creat_money'] = $trade_info['credit_money']+$prifits < 0 ?0:$trade_info['credit_money']+$prifits;
              $sdata['repay_profits'] = 0;
            }
         // print_r()
          $checkvip = Db::name("vip")->where("id",$trade_info['user_id'])->find();
           Db::startTrans();
        try{
          $log['record_vip'] = $trade_info['user_id'];
          $log['type'] = 14;
          $log['record_affect'] = $sdata['repay_creat_money']+$sdata['repay_profits'];
          $log['record_money'] = $checkvip['vip_money']+$log['record_affect'];
          $log['record_info'] = "对".$trade_info['gupiao_name']."进行卖出，返还信用金".$sdata['repay_creat_money']."元+盈利".$sdata['repay_profits']."元";
          $log['record_time'] = time();
          Db::name("vip_record")->insert($log);
          Db::name('vip')->where('id',$checkvip['id'])->setInc('vip_money',$log['record_affect']);	
           Db::commit(); 
           // return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        }
              
         
         $sdata['sell_type'] = $selltype; //后台卖出显示为1自动卖出 2shoudong 
         $sdata['sell_price'] = $nowinfos[3];
         $sdata['sell_time'] = time();
         $sdata['sell_number'] = $trade_info['deal_number'];
        // $sdata['status'] = 3; //卖出委托中
   // print_r($sdata);exit;
         $results = Db::name("trade_order")->where("id",$id)->update($sdata);
         if($results){
             settrade_log($id,"卖出委托成功");
            //给代理商返佣
            $ying_repay = $orderinfo['pay_service_money']+$orderinfo['pay_defer_money'];
             agent_repay($orderinfo['user_id'],$ying_repay);
             return true;
           // $this->success("卖出委托成功");
         }else{
             return false;
           // $this->error("卖出委托失败");
         }

 }
}