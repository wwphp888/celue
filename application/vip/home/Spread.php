<?php

namespace app\vip\home;

use app\vip\home\Index;
use think\Db;
use think\Request;

class Spread extends Index{



	public function index (){


		$spread_url = 'http://'.$_SERVER['HTTP_HOST'].url('vip/common/reg?spread='.$this->uid);

		$list = Db::name('vip')->where('spread_vip',$this->uid)->paginate(10);

		$this->assign('list', $list);//单独提取分页出来
		$this->assign('page', $list->render());//单独提取分页出来
		$this->assign('spread_url',$spread_url);

		return $this->fetch();
	}























}