<?php
namespace app\agent\home;


use think\Controller;
use think\Request;
use think\Db;
use think\helper\Hash;
session_start();

class Common extends Controller{

		public $agent_id;

		public function _initialize(){

			$agent_id = $_SESSION['agent_id'];
	
								
			if($agent_id){

				$this->agent_id = $agent_id;

				


			}





	}

	public function login(){

		if($this->agent_id){
			$this->redirect("/agent");	
		}

		if(request()->isPost()){

			$param=Request::instance()->param();
			$map['agent_username'] = $param['agent_username'];
			$agent_info=Db::name('agent')->where($map)->find();
			if(!$agent_info){

				msgreturn("","用户不存在");


			}
			if (!Hash::check((string)$param['agent_password'],$agent_info['agent_password'])) {
				 msgreturn('','密码错误');
    		}

    		$_SESSION['agent_id'] =$agent_info['id'];

    		msgreturn($agent_info['id'],'');

		}else{
			
				return $this->fetch();
		}









	}







}