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
use app\index\model\Match as MatchModel;
use think\Db;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Home
{
	
    public function index()
    {
       // 获取滚动图片
        $this->assign('slider', $this->getSlider());

        //获取公告
        $this->assign("getgonggao",$this->getartlist(9));
        //获取行情资讯
        $this->assign("getartlist",$this->getartlist(10));
        //获取合作伙伴
        $this->assign("hzhb",$this->getCooperate());
        //获取牛人排行
         $this->assign("niulist",get_niu_list($_SESSION['vip_id']));

        //推荐策略
         $this->assign("bestlist",tradebestlist());
        return $this->fetch();
    }

    public function test(){

    	$res = plugin_action('Mdsms', 'Mdsms', 'checkphonecode', ['phone' => 15064118537,'code'=>1234]);
        var_dump("111");die;
    }
    public function test2(){
        
        $res = plugin_action('Price', 'Price', 'updateorderlist');
       var_dump("111");die;
    }
	public function sb(){
    echo 1;
      $info['gupiao_code'] = '000001';
    	$price = json_decode(get_socket_info('{"req":"Trade_QueryQuote","rid":"12230","para":{"Codes" : "'.$info['gupiao_code'].'","JsonType" : 1,"Server" : 1}}'),true);
      print_r($price);
    }
 /**
     * 获取滚动图片
     * @author admin
     */
    private function getSlider()
    {
        return Db::name('cms_slider')->where('status = 1')->order('sort asc')->select();
    }
    //文章
    private function getartlist($id){
        return Db::view('cms_document d',true)
                ->view('cms_document_listinfos i','content','d.id=i.aid')
                ->where('d.cid = '.$id.' and d.status = 1')
                ->order('d.id desc,d.sort asc')
                ->limit(3)
                ->select();
    }
    //获取合作伙伴
     private function getCooperate()
    {
        $list = Db::name('cms_link')->where('status = 1 and type = 2')->order('sort desc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['logourl'] = get_file_path($value['logo']);  
        }
        return $list;
             
    }

    public function outlogin(){
        unset($_SESSION['vip_id']);
        unset($_SESSION['vip_name']);
         $this->success("退出成功","index/index");
    }
    public function check_defer(){
  
  		$trade_list=Db::name('trade_order')->where('status',2)->select();
  		foreach($trade_list as $k=>$v){
        
        		if((time()-$v['deal_time'])>86400*2){
                
                	//如果超过两天，收取手续费
                  	
                	        	$add_defer_money=round(($v['trush_number']*$v['trush_price']-$v['credit_money'])/10000*$v['defer_money'],2);
                  //print_r($add_defer_money);exit;
                  					 $repay_money =round(($v['credit_money']*$v['credit_rate'])*($v['defer_money']-6)/10000,2);
                 // print_r($repay_money);exit;
        						 agent_repay($v['user_id'],$repay_money);
                     $res = Db::name("trade_order")->where("id",$v['id'])->setInc("pay_defer_money",$add_defer_money);
        						$result=money_log($add_defer_money,$v['user_id'],20,'扣除手续费');
                				echo $result?"实盘扣除手续费ok":"实盘扣除手续费fail";
                
                
                }
          /*	if($v['defer_status']!=1){
            
            
            
            		
            	$res=InvestModel::sellorder($v['id']);
            	echo $res?"实盘卖出ok":"实盘卖出fail";
            
            }*/

        
        }

  
  }
    public function match_defer(){
  
  		$trade_list=Db::name('match_order')->where('status',2)->select();
  		foreach($trade_list as $k=>$v){
        
        		if((time()-$v['deal_time'])>86400*2){
                      //如果超过两天，收取手续费
                  $add_defer_money=round(($v['trush_number']*$v['trush_price']-$v['credit_money'])/10000*$v['defer_money'],2);


        						$result=money_log($add_defer_money,$v['user_id'],13,'扣除手续费');

                	  echo $result?"虚拟扣除手续费ok":"虚拟扣除手续费fail";
                
                
                }
          	if($v['defer_status']!=1){
            
            	 $res = MatchModel::SellOneOrder($info['id'],1);
              	echo $res?"虚拟卖出ok":"虚拟卖出fail";
            
            
            }

        
        }

  
  }
  /****************添加***********************/
  public function add_queueorder(){
  
  
  	plugin_action('Price', 'Price', 'updateorderlist');
  
  
  }
 
}
