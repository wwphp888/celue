<?php
// +----------------------------------------------------------------------
// | 股票策略系统 [ V1.02 ]
// +----------------------------------------------------------------------
// | 版权所有 2018~2022 山东软淘电子商务有限公司 [ http://www.sdruantao.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.sdruantao.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

// 为方便系统核心升级，二次开发中需要用到的公共函数请写在这个文件，不要去修改common.php文件

use think\Db;
use think\View;
use app\user\model\User;
use util\Tree;

function msgreturn($array,$error,$code=null){

    if(empty($code)){ 
	if(!empty($array)) {
        $code = 200;
    } elseif (!empty($error)) {
        $code = 400;
    }
    } 
    exit(json_encode([
        'code'  => $code,
        'data'  => $array,
        'error' => $error
    ]));


}
//分析股票交易所
function codefenxi($code){
    $one = substr($code,0,1);
     switch ($one){
            case '0':
                $d = '1';
                break;
            case '3':
                $d = '1';
            break;
            case '6':
                $d = '2';
                break;
            default: 
                $d = '0';
            break;
        }
        return $d;
}
function fenxisuo($code){
    switch (substr($code, 0, 1)) {
    case '0':
        $d = 'sz' . $code;
        break;

    case '3':
        $d = 'sz' . $code;
        break;

    case '1':
        $d = 'sz' . $code;
        break;

    case '2':
        $d = 'sz' . $code;
        break;

    case '6':
        $d = 'sh' . $code;
        break;

    case '5':
        $d = 'sh' . $code;
        break;

    case '9':
        $d = 'sh' . $code;
        break;

    default:
        $d = $code;
        break;
    }

    return $d;
}
function workernotify($type,$info){
    $data['type'] = $type;
    $data['info'] = $info;
    $data['add_time'] = time();
    $res = Db::name("notify")->insertGetId($data);
    return $res;
}

 
function money_log($money,$uid,$type,$msg){ 
  $info = Db::name("vip")->where("id",$uid)->find();

    switch ($type) { 
      case '1'://充值成功
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '2'://提现冻结
        $vipdata['vip_money'] = $info['vip_money']-$money;
        $vipdata['vip_freeze'] = $info['vip_freeze']+$money;
        break;
      case '3'://提现成功
        $vipdata['vip_money'] = $info['vip_money'];
        $vipdata['vip_freeze'] = $info['vip_freeze']-$money;
        break;
      case '4'://管理员调整
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '5'://认购扣信用金
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '6'://扣综合服务费
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '13'://扣除手续费
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '14'://平仓成功
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      case '15'://提现失败
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze']-$money;
        break;
      case '16'://认购失败返还信用金
        $vipdata['vip_money'] = $info['vip_money']+$money;
        $vipdata['vip_freeze'] = $info['vip_freeze'];
        break;
      
      
    }
    $data['record_vip']  = $uid;
    $data['type']  = $type;
    $data['record_affect']  = $money;
    $data['record_money']  = $vipdata['vip_money'];
    $data['record_info']  = $msg;
    $data['record_time']  = time();
    Db::name('vip_record')->insert($data);
    $result =Db::name('vip')->where('id',$uid)->update($vipdata);
    return $result;
}
function agent_money_log($money,$uid,$type,$msg){
   $info = Db::name("agent")->where("id",$uid)->find();
      switch ($type) { 
      case '1'://充值成功
        $vipdata['agent_money'] = $info['agent_money']+$money;
        $vipdata['agent_freeze'] = $info['agent_freeze'];
        break;
      case '2'://提现冻结
        $vipdata['agent_money'] = $info['agent_money']-$money;
        $vipdata['agent_freeze'] = $info['agent_freeze']+$money;
        break;
      case '3'://提现成功
        $vipdata['agent_money'] = $info['agent_money'];
        $vipdata['agent_freeze'] = $info['agent_freeze']-$money;
        break;
            case '7'://代理返佣
        $vipdata['agent_money'] = $info['agent_money']+$money;
        $vipdata['agent_freeze'] = $info['agent_freeze'];
        break;
      case '14'://平仓成功
        $vipdata['agent_money'] = $info['agent_money']+$money;
        $vipdata['agent_freeze'] = $info['agent_freeze'];
        break;
      case '15'://提现失败
        $vipdata['agent_money'] = $info['agent_money']+$money;
        $vipdata['agent_freeze'] = $info['agent_freeze']-$money;
        break;
      case '16'://认购失败返还信用金
        $vipdata['agent_money'] = $info['agent_money']+$money;
        $vipdata['agent_freeze'] = $info['agent_freeze'];
        break;
    }
    $data['agent_id']  = $uid;
    $data['type']  = $type;
    $data['affect_money']  = $money;
    $data['agent_money']  = $vipdata['agent_money'];
    $data['record_info']  = $msg;
    $data['recod_time']  = time();
    Db::name('agent_record')->insert($data);
    $result =Db::name('agent')->where('id',$uid)->update($vipdata);
    return $result;



}
function agent_repay($vip_id,$money){

        $code=Db::name('vip')->where('id',$vip_id)->value('recommendCode');
        //$agent_info = Db::name('agent')->where('agent_code',$code)->find();
         
        $parent = Tree::getParents(Db::name('agent')->where($where)->column('id,agent_parent as pid,agent_username as title ,agent_rate,agent_initprice'),Db::name('agent')->where('agent_code',$code)->value('id'));
        $repay_money =$parent[0]['agent_rate']/100*($money-$parent[0]['agent_initprice']);
        $parent=array_reverse($parent);
        foreach ($parent as $key => $value) {
            if($value['pid'] == 0){

               $parent[$key]['repay_money'] = $repay_money;  
            }else{   
              $item_money = $repay_money*$value['agent_rate']/100;
              $repay_money = $repay_money-$item_money; 
              $parent[$key]['has_money'] = $repay_money;
              $parent[$key]['repay_money'] = $item_money;  
            }
              
        }
       // print_r($parent);
      Db::startTrans();
      try{
        foreach ($parent as $key => $value) {
            agent_money_log($value['repay_money'],$value['id'],7,'成交返佣');
        }
        
        Db::commit();
        return true;
      }catch (\Exception $e) {


         Db::rollback();
         return false;
      }


}

//持仓操作记录添加
function settrade_log($trade_id,$msg){
  $trade_order = Db::name("trade_order")->where("id",$trade_id)->find();
  $data['vip_id'] = $trade_order['user_id'];
  $data['vip_name'] = Db::name("vip")->where("id",$trade_order['user_id'])->value("vip_name");
  $data['trade_order'] = $trade_order['order_no'];
  $data['trade_id'] = $trade_order['id'];
  $data['log'] = $msg;
  $data['add_time'] = time();
  $res = Db::name("trade_log")->insert($data);
  if($res){
    return true;
  }else{
    return false;
  }
}
//获取股票收益
function getyestprofits($type=1){
    switch ($type) {
        case '1': //总收益
            $map['status'] = 2;
            break;
        case '2': //日收益（昨日）
            $yest_start = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
            $yest_end = strtotime(date('Y-m-d'.'00:00:00',time()));
            $map['creat_time'] =array("between",[$yest_start,$yest_end]);
            $map['status'] = 2;
            break;
        case '3': //周收益（本周）
            //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $week_start=strtotime("$sdefaultDate -".($w ? $w - $first : 6).' day');
           
            //本周结束日期
            $week_end=$week_start+86400*6;
     
            $map['creat_time'] =array("between",[$week_start,$week_end]);
            $map['status'] = 2;
            break;
        case '4': //月收益
            $month_start = strtotime(date('Y-m-1'));
            $map['creat_time'] =array("between",[$month_start,time()]);
            $map['status'] = 2;
            break;
        default:
            # code...
            break;
    }
  //  echo"<pre>";
//var_dump($map);
    $res = Db::name("match_order")->field("user_id,SUM(trush_price*trush_number) as sum_shizhi,SUM(repay_profits) as sum_profits,count('id') as countid")->where($map)->order('sum_profits DESC')->limit(10)->group("user_id")->select();

    foreach ($res as $k => $v) {
      //收益率 
      $res[$k]['profits_rate'] = round($v['sum_profits']/$v['sum_shizhi']*100,2);
      $res[$k]['vip_name'] = Db::name("vip")->where('id',$v['user_id'])->value('vip_name');
      $res[$k]['head_img'] = getheadimg($v['user_id']);
      $count = Db::name("vip_rss")->where("vip_id",$_SESSION['vip_id'])->where("other_id",$v['user_id'])->count('id');

      $res[$k]['rss_status'] = $count > 0?1:0;
    }
//var_dump($res);
    return $res;
   
    
}
//获取单人模拟盘收益
function getoneprofits($id,$type=1){
    switch ($type) {
        case '1': //总收益
            $map['status'] = 2;
            break;
        case '2': //日收益（昨日）
            $yest_start = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
            $yest_end = strtotime(date('Y-m-d'.'00:00:00',time()));
            $map['creat_time'] =array("between",[$yest_start,$yest_end]);
            $map['status'] = 2;
            break;
        case '3': //周收益（本周）
            //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $week_start=strtotime("$sdefaultDate -".($w ? $w - $first : 6).' day');
           
            //本周结束日期
            $week_end=$week_start+86400*6;
     
            $map['creat_time'] =array("between",[$week_start,$week_end]);
            $map['status'] = 2;
            break;
        case '4': //月收益
            $month_start = strtotime(date('Y-m-1'));
            $map['creat_time'] =array("between",[$month_start,time()]);
            $map['status'] = 2;
            break;
        default:
            # code...
            break;
    }
    $map['user_id'] = $id;
   // var_dump($map);
    $res = Db::name("match_order")->field("user_id,SUM(trush_price*trush_number) as sum_shizhi,SUM(repay_profits) as sum_profits,count('id') as countid")->where($map)->order('sum_profits DESC')->find();
   // var_dump($res);
    //收益率 
    if($res['sum_shizhi'] > 0){
       $res['profits_rate'] = round($res['sum_profits']/$res['sum_shizhi']*100,2);
     }else{
      $res['profits_rate'] = 0;
     }
   
   
    return $res;
   
    
}

//获取单人实盘盘收益
function spgetoneprofits($id,$type=1){
    switch ($type) {
        case '1': //总收益
            $map['status'] = 2;
            break;
        case '2': //日收益（昨日）
            $yest_start = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
            $yest_end = strtotime(date('Y-m-d'.'00:00:00',time()));
            $map['creat_time'] =array("between",[$yest_start,$yest_end]);
            $map['status'] = 2;
            break;
        case '3': //周收益（本周）
            //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $week_start=strtotime("$sdefaultDate -".($w ? $w - $first : 6).' day');
           
            //本周结束日期
            $week_end=$week_start+86400*6;
     
            $map['creat_time'] =array("between",[$week_start,$week_end]);
            $map['status'] = 2;
            break;
        case '4': //月收益
            $month_start = strtotime(date('Y-m-1'));
            $map['creat_time'] =array("between",[$month_start,time()]);
            $map['status'] = 2;
            break;
        default:
            # code...
            break;
    }
    $map['user_id'] = $id;
   // var_dump($map);
    $res = Db::name("trade_order")->field("user_id,SUM(trush_price*trush_number) as sum_shizhi,SUM(repay_profits) as sum_profits,count('id') as countid")->where($map)->order('sum_profits DESC')->find();
   // var_dump($res);
    //收益率 
    if($res['sum_shizhi'] > 0){
       $res['profits_rate'] = round($res['sum_profits']/$res['sum_shizhi']*100,2);
     }else{
      $res['profits_rate'] = 0;
     }
   
   
    return $res;
   
    
}

//获取实盘数据，大赛数据并按照时间进行排序
function GetAllOrders($id){
    $newlist = array();
    $vip_name = Db::name("vip")->where("id",$id)->value("vip_name");
    $head_img = getheadimg($id);
    //大赛
    $dasai = Db::name("match_order")->where("user_id",$id)->select();
    foreach ($dasai as $key => $value) {
        $value['head_img'] = $head_img;

        $value['vip_name'] = hidecard($vip_name,2);
        $value['cztype'] = 1; //1为买入 2为卖出
        $value['czname'] = '大赛';
        $value['effect_time'] = $value['creat_time'];
        $value['yingkui'] = floatval(($value['now_price']-$value['trush_price'])*$value['trush_number']); //当前盈亏
        $value['caozuotime'] = second2string($value['creat_time'],1,1);
        $value['creat_times'] =date("Y-m-d H:i",$value['creat_time']);
        $newlist[] = $value;
        if($value['status'] == 2){
            $value['cztype'] = 2; //1为买入 2为卖出
            $value['effect_time'] = $value['sell_time'];
            $value['sell_times'] =date("Y-m-d H:i",$value['sell_time']);
            $value['profits_rate'] = round($value['repay_profits']/($value['trush_price']*$value['trush_number']),2);
            $newlist[] = $value;
        }
    }

    //实盘
    $spmap['user_id'] = $id;
    $spmap['status'] = array("in","2,4");
    $shipan = Db::name("trade_order")->where("user_id",$id)->select();
    foreach ($shipan as $key => $value) {
        $value['head_img'] = $head_img;
        $value['vip_name'] = hidecard($vip_name,2);
        $value['cztype'] = 1; //1为买入 2为卖出
        $value['czname'] = '实盘';
        $value['effect_time'] = $value['creat_time'];
        $value['yingkui'] = floatval(($value['now_price']-$value['trush_price'])*$value['trush_number']); //当前盈亏
        $value['creat_times'] =date("Y-m-d H:i",$value['creat_time']);
        $newlist[] = $value;
        if($value['status'] == 4){
            $value['cztype'] = 2; //1为买入 2为卖出
            $value['effect_time'] = $value['sell_time'];
            $value['sell_times'] =date("Y-m-d H:i",$value['sell_time']);
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
/*数据处理
*数据超过1万的则显示万元
*/
function shujuw($num){
  if($num > 10000){
    $num = round($num/10000,2)."万";
  }
  return $num;
}

/*数据隐藏处理
*
*/
function hidecard($num,$type){

    switch ($type) {
      case '1': //手机号
          $res = substr($num, 0, 3) . str_repeat("*", 4) . substr($num, strlen($num) - 4);    //手机号
        break;
      case '2': //用户名
            $mb_str = mb_strlen($num, 'UTF-8');
              if ($mb_str <= 6) {
                  $suffix = mb_substr($num, $mb_str - 1, 1, 'UTF-8');
                  $res = mb_substr($num, 0, 1, 'UTF-8') . str_repeat("*", 3) . $suffix;    //新用户名,无乱码截取
              } else {
                  $suffix = mb_substr($num, $mb_str - 3, 3, 'UTF-8');
                  $res = mb_substr($num, 0, 3, 'UTF-8') . str_repeat("*", 3) . $suffix;    //新用户名,无乱码截取
              }
        break;
      case '3': //银行卡
        $res = substr($num, 0, 3) . str_repeat("*", 12) . substr($num, strlen($num) - 4);   //身份证
        break;
      
      default:
        $res = $res;
        break;
    }
    return $res;
}
/*
* 中文截取，支持gb2312,gbk,utf-8,big5 
*
* @param string $str 要截取的字串
* @param int $start 截取起始位置
* @param int $length 截取长度
* @param string $charset utf-8|gb2312|gbk|big5 编码
* @param $suffix 是否加尾缀
*/
function cnsubstr($str, $length, $start=0, $charset="utf-8", $suffix=true)
{
     $str = strip_tags($str);
     if(function_exists("mb_substr"))
     {
         if(mb_strlen($str, $charset) <= $length) return $str;
         $slice = mb_substr($str, $start, $length, $charset);
     }
     else
     {
         $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
         $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
         $re['gbk']          = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
         $re['big5']          = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
         preg_match_all($re[$charset], $str, $match);
         if(count($match[0]) <= $length) return $str;
         $slice = join("",array_slice($match[0], $start, $length));
     }
     if($suffix) return $slice."…";
     return $slice;
}
//把秒换成小时或者天数
function second2string($second,$type=0,$now=0){
  if($second < 1){
    return false;
  }
  if($now > 0){
    $second = time()-$second;
  }
  $day = floor($second/(3600*24));
  $second = $second%(3600*24);//除去整天之后剩余的时间
  $hour = floor($second/3600);
  $second = $second%3600;//除去整小时之后剩余的时间 
  $minute = floor($second/60);
  $second = $second%60;//除去整分钟之后剩余的时间 
  
  switch($type){
    case 0:
      if($day>=1) $res = $day."天";
      elseif($hour>=1) $res = $hour."小时";
      else  $res = $minute."分钟";
    break;
    case 1:
      if($day>=28) $res = date("Y-m-d",time()+$second);
      elseif($day>=1&&$day<28) $res = $day."天前";
      elseif($hour>=1) $res = $hour."小时前";
      else  $res = $minute."分钟前";
    break;
  }
  //返回字符串
  return $res;
}
//买入委托异步通知处理
function buygupiao($info,$notifyid){
     //委托编号
      $map['trush_no'] = $info['oid'];
      $map['gupiao_code'] = $info['data']['证券代码'];

      $check = Db::name("trade_order")->where($map)->find();
      
      if(!is_array($check)){
        return false;
      }
      if($check['status'] > 1){
        return false;
      }
    
      if($check['deal_no'] != ''){
         $dede['deal_no'] = $check['deal_no']."|".$info['data']['成交编号'];
      }else{
         $dede['deal_no'] = $info['data']['成交编号'];
      }
      
      if($check['deal_number'] > 0){
         $dede['deal_number'] = $check['deal_number']+floatval($info['data']['成交数量']);
      }else{
         $dede['deal_number'] = $info['data']['成交数量'];
      }

      if($check['notify_id'] != ''){
        $dede['notify_id'] = $check['notify_id']."|".$notifyid;
      }else{
        $dede['notify_id'] = $notifyid;
      }
      $dede['deal_time'] = time();
      if($check['trush_number'] == $dede['deal_number']){
        $dede['status'] =2;//持仓中
      }
    
      $res = Db::name("trade_order")->where("id",$check['id'])->update($dede);
      if($res){
        if($notifyid > 0){
            Db::name("notify")->where("id",$notifyid)->setField('status',1);
        }
        return true;
    }else{
        if($notifyid > 0){
            Db::name("notify")->where("id",$notifyid)->setField('status',2);
        }
        return false;
    }
      
}


//卖出委托异步通知处理
function sellgupiao($info,$notifyid){
     //委托编号
      $map['sell_trush_no'] = $info['oid'];
      $map['gupiao_code'] = $info['data']['证券代码'];

      $check = Db::name("trade_order")->where($map)->find();
     
      
      if(!is_array($check)){
        return false;
      }
      if($check['status'] != '3'){
        return false;
      }
     Db::startTrans();
      if($check['sell_deal_no'] != ''){
         $dede['sell_deal_no'] = $check['sell_deal_no']."|".$info['data']['成交编号'];
      }else{
         $dede['sell_deal_no'] = $info['data']['成交编号'];
      }
      
      if($check['sell_deal_number'] > 0){
         $dede['sell_deal_number'] = $check['sell_deal_number']+floatval($info['data']['成交数量']);
      }else{
         $dede['sell_deal_number'] = $info['data']['成交数量'];
      }

      if($check['sell_notify_id'] != ''){
        $dede['sell_notify_id'] = $check['sell_notify_id']."|".$notifyid;
      }else{
        $dede['sell_notify_id'] = $notifyid;
      }
      $dede['sell_deal_time'] = time();
      if($check['sell_number'] == $dede['sell_deal_number']){
         $dede['status'] = 4;//已平仓完成
         //返还信用金及收益
         $prifits = ($check['sell_price']-$check['trush_price'])*$dede['sell_deal_number'];
         if($prifits > 0){
            $dede['repay_creat_money'] = $check['credit_money'];
            $dede['repay_profits'] = $prifits;
         }else{
            $dede['repay_creat_money'] = $check['credit_money']+$prifits;
            $dede['repay_profits'] = 0;
         }
      }

      $res = Db::name("trade_order")->where("id",$check['id'])->update($dede);

      $checkvip = Db::name("vip")->where("id",$check['user_id'])->find();

      $log['record_vip'] = $checkvip['id'];
      $log['type'] = 14;
      $log['record_affect'] = $dede['repay_creat_money']+$dede['repay_profits'];
      $log['record_money'] = $checkvip['vip_money']+$log['record_affect'];
      $log['record_info'] = "对".$check['gupiao_name']."进行卖出，返还信用金".$dede['repay_creat_money']."元+盈利".$dede['repay_profits']."元";
      $log['record_time'] = time();
      $logres = Db::name("vip_record")->insert($log);

      $upres = Db::name('vip')->where('id',$checkvip['id'])->setInc('vip_money',$log['record_affect']);

      if($res && $logres && $upres){
        Db::commit(); 
        if($notifyid > 0){
            Db::name("notify")->where("id",$notifyid)->setField('status',1);
        }
        return true;
    }else{
        Db::rollback();
        if($notifyid > 0){
            Db::name("notify")->where("id",$notifyid)->setField('status',2);
        }
        return false;
    }
}



/*获取用户名
*/
function getvip_name($id,$type=0){
  $res = Db::name("vip")->where("id",$id)->value("vip_name");
  if($type == 1){
    $res = hidecard($res,2);
  }
  return $res;
}


/*实盘  获取最优排行（分页）
*
*/
function tradebestlist(){
  $list = Db::name('trade_order')
        ->field('a.*,v.vip_phone,CONVERT(a.repay_profits/(a.trush_price*a.trush_number),decimal(18,2)) syl')
        ->alias('a')
        ->join('vip v','v.id=a.user_id')
        ->where('a.status != 1') //计算已完成订单
        ->limit(10)
        ->order('syl desc')
        ->select(); 
        foreach ($list as $key => $value) {
        $list[$key]['vip_phone'] = substr_replace($value['vip_phone'], '****', 3, 4);
        $list[$key]['deal_time'] = date('Y-m-d',$value['deal_time']);
        $list[$key]['head_img'] = getheadimg($value['user_id']);
        $list[$key]['sell_times'] = second2string($value['sell_time'],1,1);
        //$list[$key]['syl'] = round($value['repay_profits']/($value['trush_price']*$value['trush_number'])*100,2);
        } 
  return $list;
}


/*获取牛人排行
*/

function get_niu_list($vip_id){
  // $map['status'] = 2;
   $res = Db::name("trade_order")->field("user_id,SUM(trush_price*trush_number) as sum_shizhi,SUM(repay_profits) as sum_profits,count('id') as countid")->where($map)->order('sum_profits DESC')->limit(10)->group("user_id")->select();

    foreach ($res as $k => $v) {
      //收益率 
      $res[$k]['profits_rate'] = round($v['sum_profits']/$v['sum_shizhi']*100,2);
      $res[$k]['vip_name'] = Db::name("vip")->where('id',$v['user_id'])->value('vip_name');
      $res[$k]['head_img'] = getheadimg($v['user_id']);
      if($vip_id > 0){
        $count = Db::name("vip_rss")->where("vip_id",$vip_id)->where("other_id",$v['user_id'])->count('id');

        $res[$k]['rss_status'] = $count > 0?1:0;
      }
      
    }

    return $res;

}

/*  设置订阅
*/
function setRss_info($other_id,$vip_id){
  if($vip_id < 1){
    return false;
  } 
  $map['vip_id'] = $vip_id;
  $map['other_id'] = $other_id;
  $check = Db::name("vip_rss")->where($map)->count("id");
  if($check > 0){
    $res = Db::name("vip_rss")->where($map)->delete();
  }else{
    $map['add_time'] = time();
    $res = Db::name("vip_rss")->insert($map);
  }
 return $res;
}


/*
*获取订阅
*/
function get_rss_list($vip_id){
  $list = Db::name('vip_rss')->where("vip_id",$vip_id)->select();
  foreach ($list as $key => $value) {
       $res = Db::name("match_order")->where("user_id",$value['vip_id'])->select();
       $sumrate = 0;
       foreach ($res as $k => $v) {
          $sumrate += $v['repay_profits']/($v['trush_price']*$v['trush_number']);
       }
       $list[$key]['sumrate'] = round($sumrate*100,2);
       $list[$key]['head_img'] = getheadimg($value['other_id']);
       $list[$key]['vip_name'] = Db::name("vip")->where("id",$value['other_id'])->value("vip_name");
  }
return $list;
}


/*股票开市时间验证
*/
function checkopen_market(){
   //股票开市时间验证
        $nowweek = date("w");
        if($nowweek == '6' || $nowweek=='0'){
          return false;
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

           return false;
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          
           return false;
        }
        
        return true;
}


/*获取头像
*
*/
function getheadimg($id){

  $info = Db::name("vip")->where("id",$id)->value("head_img");
  if(empty($info)||$info == ''){
    $info = "/static/home/img/userhead.png";
  }else{
    $info  = "/uploads/".$info;
  }
  return $info;
}

    /*
    *sina 指数  上证（s_sh000001） 深证（s_sz399001） 沪深300（s_sh000300）
    *http://hq.sinajs.cn/rn=1520733544912&list=s_sh000001,s_sz399001,s_sh000300
    */
      function zhishu_d($code){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://hq.sinajs.cn/rn=1520733544912&list=".$code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $t2 = explode(',', mb_convert_encoding($output, "utf-8", "gbk"));
       // var_dump($t2);
       /* var_dump($t2);die;
        $t2['32']=substr($t2['0'],21);
        $t2['0']=substr($t2['0'],11,8);*/
        return $t2;
    }
        //获取持仓市值
 function gethascicang2($id){
        $map['user_id'] = $id;
        $map['status'] = array("in","1,2");
      $list = Db::name('trade_order')->field("trush_price,deal_number,now_price,yest_price,creat_time")->where($map)->select();
      $res['has_sum'] = 0;
      $res['yingkui'] = 0;
      $res['yest_yingkui'] = 0;
      foreach ($list as $key => $value) {
        $res['has_sum'] += $value['trush_price']*$value['deal_number'];

        $res['yingkui'] += ($value['now_price']-$value['trush_price'])*$value['deal_number'];
        $nowstarttime = strtotime(date("Y-m-d",time()));
        if($value['creat_time'] < $nowstarttime){
           $res['yest_yingkui'] += ($value['yest_price']-$value['trush_price'])*$value['deal_number'];
        }
       

      }
   	  $res['has_sum'] =round($res['has_sum'],2);
   	  $res['yingkui'] =round($res['yingkui'],2);
   	  $res['yest_yingkui'] =round($res['yest_yingkui'],2);
      return $res;
    }