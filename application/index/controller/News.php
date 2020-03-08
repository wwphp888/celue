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
use Think\Db;
use think\Request;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class News extends Home
{
	
    public function index(){
    	//var_dump(request()->param('id'));
    	$id = request()->param('id');
    	//侧边列表
       $this->typeleft();
       if($id < 1 || empty($id)){
       		$info = Db::name("cms_page")->where("status = 1")->order("id asc")->find();
       	}else{
       		$info = Db::name("cms_page")->where("id = {$id}")->find();
       	}
       
        $this->assign('info',$info);
       return $this->fetch();
    }

    public function liste(){
    	$id = request()->param('id');
    	//侧边列表
       $this->typeleft();

       $info = Db::name("cms_column")->where("id = {$id}")->find();
       $list = Db::name("cms_document")->where("cid = {$id}")->paginate(10);
       $page = $list->render();
       $this->assign("list",$list);
       	$this->assign('page', $page);
        $this->assign('info',$info);
       return $this->fetch();
    }

    public function detail(){
    	$id = request()->param('id');
    	//侧边列表
       $this->typeleft();

      
       $detail = Db::view('cms_document d',true)
       				->view('cms_document_listinfos i','content','d.id = i.aid')
       				->where("d.id = {$id}")
       				->find();
     
        $info = Db::name("cms_column")->where("id = {$detail['cid']}")->find();
       $this->assign("detail",$detail);
       	
        $this->assign('info',$info);
       return $this->fetch();
    }


    public function typeleft(){
    	//单页
       $danyelist = Db::name("cms_page")->where("status = 1")->select();
       $this->assign("danyelist",$danyelist);
       //列表
       $liebiaolist = Db::name("cms_column")->where("pid = 0 and hide = 0")->order("sort asc")->select();
       foreach ($liebiaolist as $key => $value) {
       		$linshi = Db::name("cms_column")->where("pid = {$value['id']} and hide = 0")->order("sort asc")->select();
       		$liebiaolist[$key]['downlist'] = $linshi;
       }
		$this->assign("liebiaolist",$liebiaolist);
    }

  

 
}
