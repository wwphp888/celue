<?php
namespace app\agent\validate;

 use think\validate;



 class Agent extends validate{



 			protected $rule = [

 				'agent_username' =>'require|unique:agent',
 				'agent_parent'=>'require',
 				'agent_password' =>'require',
 				'agent_code' =>'require|unique:agent',
 				//'agent_phone' =>'require|regex:^1\d{10}|unique:agent',
 				'agent_rate'=>'require',
 			];

 			protected $message = [
 				'agent_username.require'=>'用户名必填',
 				'agent_username.unique'=>'用户名已被使用',
 				'agent_code.require'=>'机构码必填',
 				'agent_code.unique'=>'机构码已被使用',
 				'agent_password.require'=>'密码必填',
 				'agent_parent.require'=>'所属上级必填',
 				'agent_phone.require'=>'手机号必填',
 				'agent_phone.regex'=>'手机号不正确',
 				'agent_phone.unique'=>'手机号已被注册',
 				'agent_rate.require'=>'比例返佣必填',

 			];


 			protected $scene = [
 				'update' =>  ['agent_phone','agent_username','agent_rate'],
		        'login'  =>  ['vip_name' => 'require', 'vip_password' => 'require'],
		        'reg'  =>    ['vip_name','vip_password','vip_phone'],
		    ];








 }