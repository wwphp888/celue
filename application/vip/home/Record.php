<?php

namespace app\vip\home;

use app\vip\home\Index;
use think\Db;
use think\Request;

class Record extends Index{




	public function index(){

		$map['record_vip']= $this->uid;
		if(input('type')){


			$map['type'] = input('type');
		}
		if(input('time')){


			$map['record_time'] =array('gt',date("Y-m-d",strtotime("-".input('time')." month")));
		}
		if(input('begin')&&input('end')){

			$map['record_time'] = array('between',[input('begin'),input('end')]);


		}
		$record_type =config('MEONEY_TYPE');
		
		$list = Db::name('vip_record')->where($map)->paginate(10)->each(function($item,$key)use($record_type){
			
			$item['type'] = $record_type[$item['type']];
			return $item;
		});

		//print_r($list);exit;
 		$this->assign('list', $list);//单独提取分页出来
		$this->assign('page', $list->render());//单独提取分页出来

		 return $this->fetch(); // 渲染模板


	}









}