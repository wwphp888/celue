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

namespace app\trade\home;

use app\index\controller\Home;
use app\index\model\Invest as InvestModel;
use app\index\model\Match as MatchModel;
use think\Db;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Crontab extends Home
{
	public function index(){
		die('hello');
	
	}

	//分钟处理任务
	public function minutes(){
		//效验开盘时间
		if(!check_open_pan()){
	         return false;//非交易时间
	       }
	     //查询大赛（持仓中）
	     $match_order = Db::name("match_order")->where("status",1)->select();
	     //查询实盘（持仓中）
	     $trade_order = Db::name("trade_order")->where("status",2)->select();

	     $arrlist = array();
	     foreach ($trade_order as $key => $value) {
	     	if(!in_array($value['gupiao_code'],$arrlist)){
	     		array_push($arrlist,$value['gupiao_code']);
	     	}
	     }
	     $infolist = $this->detail($arrlist);
	     foreach ($infolist as $key => $value) {
	     	$mmp['now_price'] = $value['nowprice'];
	     	$mmp['yest_price'] = $value['yest_price'];
	     	$mmp['now_price_update'] = time();
	     	Db::name("match_order")->where("gupiao_code",$value['gupiao_code'])->where("status",1)->update($mmp);
	     	Db::name("trade_order")->where("gupiao_code",$value['gupiao_code'])->where("status",2)->update($mmp);
	     }


	}

	public function expsell(){
      //效验开盘时间
		if(!check_open_pan()){
	         return false;//非交易时间
	       }
		/////////以下为强平////////
	      //查询大赛（持仓中）
	     $match_order_end = Db::name("match_order")->where("status",1)->select();
	     if(is_array($match_order_end)){
	     	foreach ($match_order_end as $key => $value) {
		     	if($value['now_price'] <= $value['stop_down'] || $value['now_price'] >= $value['stop_win']){
	             
	                 $res = MatchModel::SellOneOrder($value['id'],1);//强平
                  	echo "模拟强平";
	            }
		     }
	     }
	     //查询实盘（持仓中）
	     $trade_order_end = Db::name("trade_order")->where("status",2)->select();
	     if(is_array($trade_order_end)){
	     	foreach ($trade_order_end as $key => $value) {
		     	if($value['now_price'] <= $value['stop_down'] || $value['now_price'] >= $value['stop_win']){
	             
	                 $res = InvestModel::sellorder($value['id']);//强平
                  echo"实盘强平";
	            }
		     }
	     }
	}

	public function day(){
		//效验开盘时间
		if(!check_open_pan()){
	         return false;//非交易时间
	       }
		//实盘扣手续费
		$trade_list=Db::name('trade_order')->where('status',2)->select();
  		foreach($trade_list as $k=>$v){
        		if((time()-$v['deal_time'])>86400*2){

        			$day = date("Ymd",$v['last_pay_defer_time']);
        			$now = date("Ymd",time());
        			if($now > $day){
        				//如果超过两天，收取手续费
	                	$add_defer_money=round(($v['credit_money']*$v['credit_rate'])/10000*$v['defer_money'],2);
	                  	$repay_money =round(($v['credit_money']*$v['credit_rate'])*($v['defer_money']-6)/10000,2);
	        			agent_repay($v['user_id'],$repay_money);
	        			$savedata['pay_defer_money'] = $v['pay_defer_money']+$add_defer_money;
	        			$savedata['last_pay_defer_time'] = time();
	                    Db::name("trade_order")->where("id",$v['id'])->update($savedata);
	        			money_log(-$add_defer_money,$v['user_id'],13,$v['order_no'].'扣除递延费');
        			}
                	
                	
                }
        }
        //虚拟盘扣手续费
        $match_list=Db::name('match_order')->where('status',1)->select();
  		foreach($match_list as $k=>$v){
        		if((time()-$v['deal_time'])>86400*2){
        			$day = date("Ymd",$v['last_pay_defer_time']);
        			$now = date("Ymd",time());
        			if($now > $day){
        				  //如果超过两天，收取手续费
	                    $add_defer_money=round(($v['trush_number']*$v['trush_price']-$v['credit_money'])/10000*$v['defer_money'],2);
	        		    $log['record_vip'] = $v['user_id'];
				        $log['type'] = 12;
				        $log['record_affect'] = $add_defer_money;
				        $log['record_money'] = $v['match_money']-$log['record_affect'];
				        $log['record_info'] = "【大赛】".$v['order_no'].'递延费扣除';
				        $log['record_time'] = time();
				        $logres = Db::name("vip_record")->insert($log);

				        $upres = Db::name('match_info')->where('vip_id', $v['user_id'])->setDec('match_money',$log['record_affect']);
        			}
                    
                }
          	if($v['defer_status']!=1){
            	$res = MatchModel::SellOneOrder($v['id'],1);
              	echo $res?"虚拟卖出ok":"虚拟卖出fail";
            }
        }


	}

	public static function detail($codelist){
		if(!is_array($codelist)){
			return false;
		}
		$strcode = '';
		foreach ($codelist as $key => $value) {
			$strcode .= fenxisuo($value).",";
		}
		$strcode = substr($strcode,0,strlen($strcode)-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://qt.gtimg.cn/q=" . $strcode);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$output);
        $list2 = explode(';', mb_convert_encoding($output, "utf-8", "gbk"));
        foreach ($list2 as $key => $value) {
        	if($value == '' || empty($value)){
        		break;
        	}
        	$t2 = explode('~', mb_convert_encoding($value, "utf-8", "gbk"));
        	$ret[$key]['gupiao_code'] = $t2[2];
        	$ret[$key]['nowprice'] = $t2[3];
        	$ret[$key]['yest_price'] = $t2[4];
        }
        
        
        return $ret;
        //以下为逐笔成交数据
        /*$t2['29'] = explode('|',$t2['29']);
        $ret=[];
        foreach ($t2['29'] as $k =>$v){
            $ret[$k]=explode('/',$v);
            $tmd[$k]=$ret[$k][1];
            $ret[$k][1]=$ret[$k][2];
            $ret[$k][2]=$tmd[$k];
        }
        if($ret==[]){return null;}
        return $ret;*/
    }


}