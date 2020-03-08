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

namespace app\index\controller;
use app\index\model\Invest as InvestModel;
use think\Db;
use think\Request;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Invest extends Home
{
  public function _initialize(){

      parent::_initialize();
   

      if(!isset($_SESSION['vip_id']) ){


        $this->redirect("/vip/common/login");


      }

    }

 
	
    public function index(){

      //获取资金账户
      $vip_id = $_SESSION['vip_id'];
      $vip_info = Db::name("vip")->field("id,vip_money")->where("id = {$vip_id}")->find();
      $this->assign("vip_info",$vip_info);

      //获取持仓市值及盈亏
      $this->assign("gethas",$this->gethascicang());

        //获取信用金列表
       $strategy_tj = module_config('trade.strategy_credit_rec');
       $strategy_list = explode("|",$strategy_tj);
       $this->assign("strategy_list",$strategy_list);
       //获取信用金倍率
        $strategy_rate = module_config('trade.strategy_rate');
       $strategy_rate_list = explode("|",$strategy_rate);
       $this->assign("strategy_rate_list",$strategy_rate_list);
       //获取递延费
       $strategy_renewal_fee = module_config('trade.strategy_renewal_fee');
       $this->assign('strategy_renewal_fee',$strategy_renewal_fee);

       //获取综合服务费
       $strategy_fee = module_config('trade.strategy_fee');
       $this->assign('strategy_fee',$strategy_fee);
       //$this->assign('deal_number',explode('|',config('deal_number')));
      /* //获取止盈比例
       $winstop = module_config('trade.winstop');
       $this->assign('winstop',$winstop);

       //获取止损比例
       $downstop = module_config('trade.downstop');
       $this->assign('downstop',$downstop);*/
       

       return $this->fetch();
    }

       //加入自选
    public function add_gprss(){
      $parms = request()->param();
      if($parms['gupiao_code'] == ''|| $parms['gupiao_name'] == ''){
         return json(['status'=>0,'message'=>'参数有误，请重新提交']);
      }
      $parms['vip_id'] = $_SESSION['vip_id'];
      $check = Db::name("gupiao_rss")->where("gupiao_code",$parms['gupiao_code'])->where("vip_id",$parms['vip_id'])->select();
      $parms['add_time'] = time();
      if(count($check)>0){
        $res = Db::name("gupiao_rss")->where("id",$check[0]['id'])->delete();
        if($res){
          return json(['status'=>1,'message'=>'删除自选成功']);
        }else{
          return json(['status'=>0,'message'=>'删除自选失败']);
        }
      }else{
        $res = Db::name("gupiao_rss")->insert($parms);
        if($res){
          return json(['status'=>1,'message'=>'添加自选成功']);
        }else{
          return json(['status'=>0,'message'=>'添加自选失败']);
        }
      }
      
      
    }
    //获取持仓市值
    private function gethascicang(){
        $map['user_id'] = $_SESSION['vip_id'];
        $map['status'] = array("in","1,2");
      $list = Db::name('trade_order')->field("trush_price,deal_number,now_price,yest_price,creat_time")->where($map)->select();
      $res['has_sum'] = 0;
      $res['yingkui'] = 0;
      $res['yest_yingkui'] = 0;
      foreach ($list as $key => $value) {
        $res['has_sum'] += $value['trush_price']*$value['deal_number'];

        $res['yingkui'] += round(($value['now_price']-$value['trush_price'])*$value['deal_number'],2);
        $nowstarttime = strtotime(date("Y-m-d",time()));
        if($value['creat_time'] < $nowstarttime){
           $res['yest_yingkui'] += round(($value['yest_price']-$value['trush_price'])*$value['deal_number'],2);
        }
       

      }
      return $res;
    }
     //获取持仓单个详情
    public function gettradeorder(){
      $id = intval($_GET['id']);
      if($id < 1){
        return false;
      }
      $field = 'id,now_price,credit_money,credit_rate,stop_win,stop_down,trush_price,trush_number,defer_status';
      $info = Db::name("trade_order")->field($field)->where("id",$id)->find();
      return json($info);
    }

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
              return json(['status'=>0,'message'=>'止盈价格不得高于等于当前价']);
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

      //获取持仓列表
    public function gettradelist(){
      $status = array("in",'1,2');
      $list = Db::name("trade_order")->where("user_id",$_SESSION['vip_id'])->where("status",$status)->order("id desc")->select();
      foreach ($list as $k => $v) {
        $list[$k]['has_day'] = floor((time()-$v['creat_time'])/86400); //已持仓天数，向下取整
        $list[$k]['yingkui'] = round(($v['now_price']-$v['trush_price'])*$v['trush_number'],2); //当前盈亏
        if($list[$k]['yingkui'] < 0){
           $list[$k]['stop_win_price'] = round($v['now_price']-$v['stop_down'],2);
        }else{
           $list[$k]['stop_win_price'] = round($v['stop_win'] - $v['now_price'],2);
        }
        $list[$k]['create_times'] = date("Y-m-d H:i:s",$v['creat_time']);
        if($v['sell_time'] > 1){
           $list[$k]['sell_times'] = date("Y-m-d H:i:s",$v['sell_time']);
        }
        if($v['sell_type'] >0){
          $list[$k]['sell_types'] = $v['sell_type'] == 1?'自动卖出':'手动卖出';
        }
       
      }
      //var_dump($list);
     
      return json($list);
    }
 //修改递延费状态
    public function setswitch(){
        $id = intval($_GET['id']);
        $status = intval($_GET['status'])>0?intval($_GET['status']):0;
        $res = Db::name("trade_order")->where("id",$id)->setField("defer_status",$status);
        if($res){
          return json(['status'=>1,'message'=>'设置成功']);
        }else{
          return json(['status'=>0,'message'=>'设置失败']);
        }

    }
    //获取股票列表
    public function searchStock(){
    	$key = trim($_GET['str']);
    	$map['title|code|pinyin'] = array('like', '%'.$key.'%');

    	$list = InvestModel::GetGupiaolist($map);

    	return json($list);

    }
  

    //获取实时五档行情
    public function getsocketinfo(){
      $code = trim($_POST['stcode']);
      $stocke =  module_config('trade.stockinfos');
      if($stocke == 1){
        $list = get_code_info($code);
      }else{
        $list[1] = sina_market_bs($code);
      }
      
      //$list = json_decode($res,true);
       $count = Db::name("gupiao_rss")->where("vip_id",$_SESSION['vip_id'])->where("gupiao_code",$code)->count("id");
      $list[1]['rss'] = $count;
      return json($list[1]);
    }

    //获取信用倍率（弃用）
    public function getstrategy_rate(){
         $strategy_rate = module_config('trade.strategy_rate');
       $strategy_rate_list = explode("|",$strategy_rate);
       return json($strategy_rate_list);
    }

    //获取止盈止损比例
    public function tradeinit(){
        //获取止盈比例
       $data['winstop'] = module_config('trade.winstop');
       //获取止损比例
       $data['downstop'] = module_config('trade.downstop');
       //获取信用金倍率
        $strategy_rate = module_config('trade.strategy_rate');
        //获取综合服务费
        $strategy_fee = module_config('trade.strategy_fee');
       //$strategy_fee = explode('|',config('deal_number'));
       $strategy_rate_list = explode("|",$strategy_rate);
       $data['strategy_rate'] = $strategy_rate_list[0];
       $data['strategy_rate_list'] = $strategy_rate_list;
       $data['strategy_fee'] = $strategy_fee;
       return json($data);
    }

    //提交策略
    public function tradebuy(){
      
        $vip_id = $_SESSION['vip_id'];
        $parms = $_POST;
        //股票开市时间验证
       if(!check_open_pan()){
         return json(['status'=>0,'message'=>'非交易时间']);
       }
      //验证账户余额
        //获取综合服务费
        $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("vip")->where("id",$vip_id)->find();
        if($checkvip['vip_money'] < $parms['money']+$strategy_fee){
           return json(['status'=>0,'message'=>'账户余额不足，请先充值']);
        }

        if($parms['money']< 1 || $parms['strategy_rate'] < 1||$parms['winstops']== '0' || $parms['downstops'] == '0'){
           return json(['status'=>0,'message'=>'参数有误，请重新提交']);
        }
        if($parms['number'] < 100 || fmod($parms['number'],100) > 0){
          return json(['status'=>0,'message'=>'委托数量有误，请刷新页面后重新选择提交']);
        }
        //效验用户认购类型
        if($checkvip['buy_type'] < 1){
              $parms['buy_type'] = 0;
               //计算委托数量
                $EType = codefenxi($parms['stcode']);
           
                $toparms = '{"req":"Trade_CommitOrder","rid":"1007","para":[{ "Code" : "'.$parms['stcode'].'", "Count" : '.$parms['number'].', "EType" : '.$EType.', "OType" : 1, "PType" : 1, "Price" : "'.$parms['price'].'" } ] }';
                $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($toparms));
                if(empty($res) || $res == false){
                   return json(['status'=>0,'message'=>'交易服务通讯失败，请联系管理员处理']);
                 }
              //  $res = '{"event":"Trade_SendOrderEvent","rid":7,"sid":"21","cid":"0","data":[{"委托编号":"1051","返回信息":"","检查风险标志":"0","保留信息":"","(参数)操作数据":"","句柄":""}]}';
                $info = json_decode($res,true);
               if(isset($info['data']['ErrInfo'])){
                 return json(['status'=>0,'message'=>$info['data']['ErrInfo']]);
              }
              
              if(isset($info['data'][0]['委托编号'])){
                  if($info['data'][0]['委托编号'] < 1){
                     return json(['status'=>0,'message'=>'委托失败，请重新委托']);
                  }

              }
              if(isset($info['data'][0]['委托编号'])){
                  if($info['data'][0]['委托编号'] < 1){
                     return json(['status'=>0,'message'=>'委托失败，请重新委托']);
                  }

              }
              if(isset($info['data']['ErrInfo']) || empty($res) || $res == false){
                 return json(['status'=>0,'message'=>'委托失败2']);
              }
              $parms['bianhao'] = $info['data'][0]['委托编号'];
        }else{
           $parms['buy_type'] = 1;

        }

      	$repay_money = ($parms['price']*$parms['number'])/10000*$strategy_fee;
        $res = InvestModel::SetTradeOrder($parms);

        if($res){
            agent_repay($vip_id,$repay_money);
            return json(['status'=>1,'message'=>'委托成功']);
        }else{
            return json(['status'=>0,'message'=>'委托失败']);
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

        if($nowtime < $time930 || $nowtime > $time1500){

           return json(['status'=>0,'message'=>'暂未开盘']);
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          
           return json(['status'=>0,'message'=>'暂未开盘']);
        }
         $res = InvestModel::sellorder($id);
        if($res){
            return json(['status'=>1,'message'=>'卖出委托成功']);
        }else{
            return json(['status'=>0,'message'=>'卖出委托失败']);
        }


    }


    public function celuemsg(){
            $info = get_advert(2);
            return $info;
          }
 
}
