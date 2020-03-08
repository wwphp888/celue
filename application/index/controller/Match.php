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
use app\index\model\Match as MatchModel;
use Think\Db;
use think\Request;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Match extends Home
{
	public function _initialize(){

      parent::_initialize();
   

      if(!isset($_SESSION['vip_id']) ){


        $this->redirect("/vip/common/login");


      }
      

    }
    public function index(){
      $vip_id = $_SESSION['vip_id'];
      $match_info = Db::name("match_info")->where("vip_id",$vip_id)->find();
      $this->assign("match_info",$match_info);
      //昨日收益
      $yest_start = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
      $yest_end = strtotime(date('Y-m-d'.'00:00:00',time()));
      $map['creat_time'] =array("between",[$yest_start,$yest_end]);
      $map['user_id'] = $vip_id;
      $map['status'] = 2;
      $yest_porify = Db::name("match_order")->where($map)->sum('repay_profits');

      $this->assign("yest_porify",$yest_porify);
      //总收益排行
      $this->assign("zsypaihang",getyestprofits(1));
      //日收益排行
      $this->assign("rsypaihang",getyestprofits(2));
      //周收益排行
      $this->assign("wsypaihang",getyestprofits(3));
      //月收益排行
      $this->assign("ysypaihang",getyestprofits(4));

     $this->assign("rsy",getyestprofits(2));
       $this->assign("wsy",getyestprofits(3));
      $this->assign("ysy",getyestprofits(4));
       
       return $this->fetch();
    }

     public function create(){
      
     	//获取大赛资金账户
     	$vip_id = $_SESSION['vip_id'];
     	$match_info = Db::name("match_info")->where("vip_id = {$vip_id}")->find();
     	$this->assign("match_info",$match_info);

     	//获取大赛持仓市值及盈亏
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

       //获取大赛持仓列表
      // $this->assign('cclist',$this->getcclist());

      /* //获取止盈比例
       $winstop = module_config('trade.winstop');
       $this->assign('winstop',$winstop);

       //获取止损比例
       $downstop = module_config('trade.downstop');
       $this->assign('downstop',$downstop);*/
       

       return $this->fetch();
    }

    //个人主页
    public function userhome(){
      $id = request()->param('id');
      $id = $id < 1?$_SESSION['vip_id']:$id;
      $info = MatchModel::GetUserHome($id);
      $this->assign("info",$info);
      //实盘
      $shipan = spgetoneprofits($id,1);
      $this->assign("shipan",$shipan);
      //实盘日
      $shipanr = spgetoneprofits($id,2);
      $this->assign("shipanr",$shipanr);

      //实盘周
      $shipanw = spgetoneprofits($id,2);
      $this->assign("shipanw",$shipanw);

      //实盘月
      $shipany = spgetoneprofits($id,2);
      $this->assign("shipany",$shipany);

      //大赛
      $dasai = getoneprofits($id,1);
      $this->assign("dasai",$dasai);
      //大赛日
      $dasair = getoneprofits($id,2);
      $this->assign("dasair",$dasair);

      //大赛周
      $dasaiw = getoneprofits($id,2);
      $this->assign("dasaiw",$dasaiw);

      //大赛月
      $dasaiy = getoneprofits($id,2);
      $this->assign("dasaiy",$dasaiy);

      $list = GetAllOrders($id);
    
      $this->assign("list",$list);

      return $this->fetch();
    }
    //获取大赛持仓列表
    public function getcclist(){
      $status = intval($_GET['status']);
      if($status < 1) return false;
      $list = Db::name("match_order")->where("user_id",$_SESSION['vip_id'])->where("status",$status)->select();
      foreach ($list as $k => $v) {
        $list[$k]['has_day'] = floor((time()-$v['create_time'])/86400); //已持仓天数，向下取整
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

    //获取大赛持仓单个详情
    public function getmatchorder(){
      $id = intval($_GET['id']);
      if($id < 1){
        return false;
      }
      $info = Db::name("match_order")->where("id",$id)->find();
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
      $info = Db::name("match_order")->where("id",$id)->find();
      if($type == 1){ //止盈
         if($val <= $info['now_price']){
              return json(['status'=>0,'message'=>'止盈价格不得低于当前价']);
          }
          if($val == $info['stop_win']){
              return json(['status'=>0,'message'=>'新止盈价格不得之前止盈价格相同']);
          }
         $res = Db::name("match_order")->where("id",$id)->setField("stop_win",$val);
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
          $res = MatchModel::SetStopdownPrice($id,$val,$credit);

      }else{
        return json(['status'=>0,'message'=>'参数有误，请重新提交']);
      }
      if($res){
        return json(['status'=>1,'message'=>'设置成功']);
      }else{
        return json(['status'=>0,'message'=>'设置失败，请重试']);
      }

    }

    //获取股票列表
    public function searchStock(){
    	$key = trim($_GET['str']);
    	$map['title|code|pinyin'] = array('like', '%'.$key.'%');

    	$list = MatchModel::GetGupiaolist($map);

    	return json($list);

    }
  
  //获取实时五档行情
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
       $strategy_rate_list = explode("|",$strategy_rate);
       $data['strategy_rate'] = $strategy_rate_list[0];
       $data['strategy_rate_list'] = $strategy_rate_list;
       $data['strategy_fee'] = $strategy_fee;
       return json($data);
    }
    //修改递延费状态
    public function setswitch(){
        $id = intval($_GET['id']);
        $status = intval($_GET['status'])>0?intval($_GET['status']):0;
        $res = Db::name("match_order")->where("id",$id)->setField("defer_status",$status);
        if($res){
          return json(['status'=>1,'message'=>'设置成功']);
        }else{
          return json(['status'=>0,'message'=>'设置失败']);
        }

    }

    //获取持仓市值
    private function gethascicang(){
        $map['user_id'] = $_SESSION['vip_id'];
        $map['status'] = 1;
    	$list = Db::name('match_order')->field("trush_price,trush_number,now_price,yest_price,creat_time")->where($map)->select();
      $res['has_sum'] = 0;
      $res['yingkui'] = 0;
      $res['yest_yingkui'] = 0;
    	foreach ($list as $key => $value) {
    		$res['has_sum'] += $value['trush_price']*$value['trush_number'];

        $res['yingkui'] += ($value['now_price']-$value['trush_price'])*$value['trush_number'];
         $nowstarttime = strtotime(date("Y-m-d",time()));
        if($value['creat_time'] < $nowstarttime){
            $res['yest_yingkui'] += ($value['yest_price']-$value['trush_price'])*$value['trush_number'];
          }

    	}
    	return $res;
    }

  

    //提交策略
    public function tradebuy(){
        $parms = $_POST;
        
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
      
      //验证账户余额
        //获取综合服务费
        $strategy_fee = module_config('trade.strategy_fee');

        $checkvip = Db::name("match_info")->where("vip_id = 2")->find();
        if($checkvip['match_money'] < $parms['money']+$strategy_fee){
           return json(['status'=>0,'message'=>'账户余额不足，请先充值']);
        }

        if($parms['money']< 1 || $parms['strategy_rate'] < 1||$parms['winstops']== '0' || $parms['downstops'] == '0'){
           return json(['status'=>0,'message'=>'参数有误，请重新提交']);
        }
   
        $res = MatchModel::SetMatchOrder($parms);
        if($res){
            return json(['status'=>1,'message'=>'委托成功']);
        }else{
            return json(['status'=>0,'message'=>'委托失败']);
        }
    }


//卖出
    public function subsell(){
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
         $res = MatchModel::SellOneOrder($id);
        if($res){
            return json(['status'=>1,'message'=>'卖出成功']);
        }else{
            return json(['status'=>0,'message'=>'卖出失败']);
        }

      

    }
   public function oldinfo(){
      $id = request()->post("id");
      $info = Db::name("match_order")->where("id",$id)->find();
      $info['sell_types'] = $info['sell_type'] == 1 ?'自动卖出':'手动卖出';
      $info['deal_times'] = date("Y-m-d H:i:s",$info['deal_time']);
      $info['sell_times'] = date("Y-m-d H:i:s",$info['sell_time']);
      $info['chi_day'] = ceil(($info['sell_time']-$info['deal_time'])/86400);
      $info['all_money'] = round($info['trush_number']*$info['trush_price'],2);
      return $info;
     }

 public function celuemsg(){
            $info = get_advert(2);
            return $info;
          }
 
}
