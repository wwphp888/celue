<?php

namespace app\index\model;

use think\Model as ThinkModel;
use think\Db;
/**
 * 股票交易模型
 * @package app\Invest\model
 */
class Match extends ThinkModel
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

    public static function SetMatchOrder($parms){
        $vip_id = $_SESSION['vip_id'];
        //获取当前五档行情
        $now_infos = self::getwudang($parms['stcode']);
         //获取递延费
       $strategy_renewal_fee = module_config('trade.strategy_renewal_fee');
       //获取综合服务费
       $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("match_info")->where("vip_id",$vip_id)->find();
        if($checkvip['match_money'] < $parms['money']+$strategy_fee){
            return false;
        }
        //委托数量计算
        $trush_number = intval($parms['money']*$parms['strategy_rate']/$now_infos[3]/100)*100;
        if($trush_number < 100){
            return false;
        }
        Db::startTrans();
        $data['order_no'] = "MN".time().rand(100,999);
        $data['user_id'] = $vip_id;
        $data['gupiao_name'] = $parms['stname'];
        $data['gupiao_code'] = $parms['stcode'];
        $data['now_price'] = $now_infos[3]; //当前价
        $data['yest_price'] = $now_infos[3]; //昨收价(当购买天数不足一天时显示为当前价，且不必更改)
        $data['status'] = 1; // 1为持仓中 模拟盘无委托中
        $data['credit_money'] = $parms['money'];
        $data['credit_rate'] = $parms['strategy_rate'];
        $data['stop_win'] = $parms['winstops'];
        $data['stop_down'] = $parms['downstops'];
        $data['trush_price'] = $now_infos[3]; //委托价格，为当前价
        $data['trush_number'] = $trush_number; //委托数量
        $data['service_money'] = $strategy_fee; //综合服务费
        $data['defer_money'] = $strategy_renewal_fee;//递延费
        if($parms['autostatus'] == 1){
            $data['defer_status'] = 1;
        }else{
            $data['defer_status'] = 0;
        }
        $data['creat_time'] = time();
        $res = Db::name("match_order")->insert($data);
        


        $log['record_vip'] = $vip_id;
        $log['type'] = 8;
        $log['record_affect'] = $data['credit_money']+$data['service_money'];
        $log['record_money'] = $checkvip['match_money']-$log['record_affect'];
        $log['record_info'] = "【大赛】对".$data['gupiao_name']."进行认购";
        $log['record_time'] = time();
        $logres = Db::name("vip_record")->insert($log);

        $upres = Db::name('match_info')->where('vip_id', $vip_id)->setDec('match_money',$log['record_affect']);

        if($res && $logres && $upres){
            
            Db::commit(); 
            return true;
        }else{

            Db::rollback();
            return false;
        }





    }

//调整止损
public static function SetStopdownPrice($id,$val,$credit){
    $info = Db::name("match_order")->where("id",$id)->find();
    $match_info = Db::name("match_info")->where("vip_id",$info['user_id'])->find();
    if($credit >0){
        Db::startTrans();
        $yingcredit = $info['credit_money']+$credit;
        $res = Db::name("match_order")->where("id",$id)->update(['credit_money'=>$yingcredit,"stop_down"=>$val]);
         $log['record_vip'] = $info['user_id'];
         $log['type'] = 10; //大赛保证金追加
         $log['record_affect'] = $credit;
         $log['record_money'] = $match_info['match_money']-$log['record_affect'];
         $log['record_info'] = "【大赛】对".$info['gupiao_name']."进行追加保证金";
         $log['record_time'] = time();
         $logres = Db::name("vip_record")->insert($log);

         $upres = Db::name('match_info')->where('vip_id', $info['user_id'])->setDec('match_money',$log['record_affect']); 
         if($res && $logres && $upres){
             Db::commit(); 
             return true;
         }else{
            Db::rollback();
            return false;
         }

    }else{
         $res = Db::name("match_order")->where("id",$id)->setField("stop_down",$val);
         if($res){
            return true;
         }else{
            return false;
         }
    }

    

}

//卖出
public static function SellOneOrder($id,$sell_type = 2){
     //$vip_id = $_SESSION['vip_id'];
    //获取订单
   $info = Db::name("match_order")->where("id",$id)->find();
        //获取当前五档行情
    $now_infos = self::getwudang($info['gupiao_code']);
    if($now_infos[3] <= 0){
        return false;
    }
     $match_info = Db::name("match_info")->where("vip_id",$info['user_id'])->find();
     //计算亏损
     $kuisun = floatval(($now_infos[3]-$info['trush_price'])*$info['trush_number']);
      Db::startTrans();
    try{
     if($kuisun > 0){
        $data['repay_creat_money'] = $info['credit_money'];
        $data['repay_profits'] = $kuisun;
     }else{
         $data['repay_creat_money'] = $info['credit_money']+$kuisun;
         $data['repay_profits'] = 0;
     }
     $data['status'] = 2;
     $data['sell_type'] = $sell_type; //手动卖出
     $data['sell_price'] = $now_infos[3];
     $data['sell_time'] = time();
     $data['sell_number'] = $info['trush_number'];
    Db::name("match_order")->where("id",$id)->update($data);
    
        $log['record_vip'] = $info['user_id'];
        $log['type'] = 9;
        $log['record_affect'] = $data['repay_creat_money']+$data['repay_profits'];
        $log['record_money'] = $match_info['match_money']-$log['record_affect'];
        $log['record_info'] = "【大赛】对".$info['gupiao_name']."进行委托卖出";
        $log['record_time'] = time();
        Db::name("vip_record")->insert($log);

        Db::name('match_info')->where('vip_id', $vip_id)->setInc('match_money',$log['record_affect']);

          Db::commit(); 
        return true;
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return false;
    }
    
     

}

//获取个人战绩展示
public static function GetUserHome($id){
    $info = Db::name("vip")->field("id,vip_name,last_login_time")->where("id",$id)->find();
    $info['hotnum'] = Db::name("vip_rss")->where("other_id",$id)->count("id");
    $count = Db::name("vip_rss")->where("vip_id",$_SESSION['vip_id'])->where("other_id",$id)->count('id');
    $info['rss_status'] = $count > 0?1:0;

    return $info;

}

//获取实盘数据，大赛数据并按照时间进行排序
public static function GetAllOrders($id){
    $newlist = array();
    
    //大赛
    $dasai = Db::name("match_order")->where("user_id",$id)->select();
    foreach ($dasai as $key => $value) {
        $value['cztype'] = 1; //1为买入 2为卖出
        $value['czname'] = '大赛';
        $value['effect_time'] = $value['creat_time'];
        $value['yingkui'] = floatval(($value['now_price']-$value['trush_price'])*$value['trush_number']); //当前盈亏
        $value['caozuotime'] = second2string($value['creat_time'],1,1);
        $newlist[] = $value;
        if($value['status'] == 2){
            $value['cztype'] = 2; //1为买入 2为卖出
            $value['effect_time'] = $value['sell_time'];
            $value['profits_rate'] = round($value['repay_profits']/($value['trush_price']*$value['trush_number']),2);
            $newlist[] = $value;
        }
    }

    //实盘
    $spmap['user_id'] = $id;
    $spmap['status'] = array("in","2,4");
    $shipan = Db::name("trade_order")->where("user_id",$id)->select();
    foreach ($shipan as $key => $value) {
        $value['cztype'] = 1; //1为买入 2为卖出
        $value['czname'] = '实盘';
        $value['effect_time'] = $value['creat_time'];
        $value['yingkui'] = floatval(($value['now_price']-$value['trush_price'])*$value['trush_number']); //当前盈亏
        $newlist[] = $value;
        if($value['status'] == 4){
            $value['cztype'] = 2; //1为买入 2为卖出
            $value['effect_time'] = $value['sell_time'];
            $value['profits_rate'] = round($value['repay_profits']/($value['trush_price']*$value['trush_number'])*100,2);
            $newlist[] = $value;
        }
    }

    $age = [];
    foreach($newlist as $key => $val) {
        $age[] = $val['effect_time'];
    }
     
    //冒泡排序
    for($i = 0; $i < count($age) - 1; $i++) {
        for($j = 0; $j < count($age) - $i - 1; $j++) {
            if($age[$j] < $age[$j+1]) {
                $t = $age[$j];
                $age[$j] = $age[$j+1];
                $age[$j+1] = $t;
            }
        }
    }
     
    $new = [];
    foreach($age as $key => $val) {
        foreach($newlist as $k => $v) {
            if($val == $v['effect_time']) {
                $new[$key] = $v;
                unset($newlist[$k]);
            }
        }
    }

    return $new;
}


  private function getwudang($code){
      $parms = '{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$code.'","JsonType" : 1,"Server" : 1}}';
      $res = get_socket_info($parms);
      $list = json_decode($res,true);
      return $list['data']['1'];
    }


 
}