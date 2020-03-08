<?php

namespace app\vip\home;

use app\vip\home\Index;
use think\Db;
use think\Request;

class Message extends Index{



	public function index (){

		$list = Db::name("vip_innermsg")->where("vip_id",$this->uid)->order("id desc")->paginate(10);
		// 获取分页显示
		$page = $list->render();
		// 模板变量赋值
		$this->assign('list', $list);
		$this->assign('page', $page);

		return $this->fetch();
	}
	public function readend (){

		$list = Db::name("vip_innermsg")->where("vip_id",$this->uid)->where("status",1)->order("id desc")->paginate(10);
		// 获取分页显示
		$page = $list->render();
		// 模板变量赋值
		$this->assign('list', $list);
		$this->assign('page', $page);

		return $this->fetch();
	}
	public function news (){

		$list = Db::name("vip_innermsg")->where("vip_id",$this->uid)->where("status",0)->order("id desc")->paginate(10);
		// 获取分页显示
		$page = $list->render();
		// 模板变量赋值
		$this->assign('list', $list);
		$this->assign('page', $page);

		return $this->fetch();
	}

	public function read(){
		$id = request()->post("id");
		$data['status'] = 1;
		$data['read_time'] = time();
		$res = Db::name("vip_innermsg")->where("id",$id)->update($data);
		if($res){
			return  json(['status'=>1,'message'=>'已设为已读']);	
		}else{
			return  json(['status'=>0,'message'=>'设置已读失败']);	
		}
	}



}