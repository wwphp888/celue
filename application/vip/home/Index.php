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

namespace app\vip\home;

use app\index\controller\Home;
use think\Db;
use think\Request;
session_start();
/**
 * 前台首页控制器
 * @package app\cms\admin
 */
class Index extends Home
{
    /**
     * 首页
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public $uid;
    public function _initialize(){

    	parent::_initialize();
   

    	if(!isset($_SESSION['vip_id']) ){


    		$this->redirect("/vip/common/login");


    	}
    	$this->uid = $_SESSION['vip_id'];
    	$info = 	Db::name('vip')->where('id='.$this->uid)->field('vip_password,vip_paypassword',true)->find();
        $newmsg = Db::name("vip_innermsg")->where("vip_id",$this->uid)->where("status",0)->count("id");
        $this->assign("newmsg",$newmsg);
        $this->assign('info',$info);

    }
    public function index()
    {
        	

       return $this->fetch(); // 渲染模板
    }
    public function get_trade_order(){

    	$map =[];
      $map['user_id'] = $this->uid;
    	if(input('get.type')){

    		$map['status'] = input('get.type');
    	}
    	$record = Db::name('trade_order')->where($map);
    	
    	if (input('get.number')){
    	$record = $record->limit(input('get.number'));
    	}else{
    	$record = $record->limit(10);	
    	}

    	if (input('get.page')){
    	$record =$record->page(input('get.page'));
    	}else{
    	$record =$record->page(1);
    	}
    
    	$record = $record->select();
    
    	foreach ($record as $key => $value) {
    		 // $res = get_socket_info('{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$value['gupiao_code'].'","JsonType" : 1,"Server" : 1}}');
    		  		
    		  $record[$key]['yingkui'] = ($value['now_price']-$value['buy_price'])*$value['number'];
               $record[$key]['create_time'] = date("Y-m-d H:i:s",$value['creat_time']);
    	}
    	return json($record);
    }

    public function headimg(){
        //var_dump($_FILES);
       // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('avatar_file');
      //  var_dump($file);die;
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $urls = $info->getSaveName();
                $res = Db::name("vip")->where("id",$this->uid)->setField("head_img",$urls);
                $res = false;
                if($res){
                    $data['result'] = $urls;
                    echo json_encode($data);
                }else{
                    $data['result'] = "上传失败";
                    echo json_encode($data);
                }
                
                // 成功上传后 获取上传信息
                // 输出 jpg
                //echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
               // echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
              //  echo $info->getFilename(); 
            }else{
                // 上传失败获取错误信息
              //  echo $file->getError();
            }
        }
    }
}