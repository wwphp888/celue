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
use app\index\model\Ace as AceModel;
use Think\Db;
use think\Request;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Ace extends Home
{
	
    public function index(){
     
       $this->assign("bestlist",tradebestlist());
       $this->assign("niulist",get_niu_list($_SESSION['vip_id']));
 		   $this->assign("rsslist",get_rss_list($_SESSION['vip_id']));
    
      // echo "<pre>";
      // var_dump($res);
       return $this->fetch();
    }

    public function setRss(){
    	$other_id = request()->param('id');
    	$res = setRss_info($other_id,$_SESSION['vip_id']);
    	
    	 return json($res);
    }
  

 
}
