<?php

namespace app\agent\controller;

use app\common\controller\Admin;
use think\Request;
use think\Db;

class Apicommon extends Admin{

		private $agent_info;
		public function _initialize(){

				parent::_initialize();

				$header = Request::instance()->header();
				$authKey = $header['authorization'];
				$cache = cache('Auth_'.$authKey);
				 // 校验sessionid和authKey
				
		        if (empty($authKey)||empty($cache)) {
		            //header("HTTP/1.1 999 token");
		         	msgreturn('','登录已过期',999); 

		            
		        }
		        cache('Auth_'.$authKey, $cache, 1644);
		     	$this->agent_info = $cache['agent_info'];   

		}
		public function get_agent_info(){


		

			return msgreturn($this->agent_info,'');  


		}
		public function get_member(){

			$map=[];
			if($this->param['keyword']){

			$map['vip_realname']=array('like','%'.$this->param['keyword'].'%');
			$map['vip_phone']=array('like','%'.$this->param['keyword'].'%');		

			}
			$list = Db::name('vip')->field('id,vip_name,vip_phone,vip_realname,vip_money')->whereOr($map);
			if($this->param['page']){

				$list= $list->page($this->param['page'],10);

			}	
			$list= $list->select();
			$data['list'] = $list;
			$data['count'] = Db::name('vip')->whereOr($map)->count('id');
			return msgreturn($data,'');
		}











}