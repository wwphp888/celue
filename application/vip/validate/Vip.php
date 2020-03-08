<?php
namespace app\vip\validate;

 use think\validate;



 class Vip extends validate{



 			protected $rule = [

 				'vip_name' =>'require|unique:vip',
 				'vip_password' =>'require',
 				'vip_paypassword' =>'require',
 				'vip_phone' =>'require|regex:^1\d{10}|unique:vip',
 				'recommendCode'=>'require',
 			];

 			protected $message = [
 				'vip_name.require'=>'用户名必填',
 				'vip_name.unique'=>'用户名已被使用',
 				'vip_password.require'=>'密码必填',
 				'vip_paypassword.require'=>'支付密码必填',
 				'vip_phone.require'=>'手机号必填',
 				'vip_phone.regex'=>'手机号不正确',
 				'vip_phone.unique'=>'手机号已被注册',
 				'recommendCode.require'=>'推荐码必填',

 			];


 			protected $scene = [
 				'update' =>  ['vip_phone'],
		        'login'  =>  ['vip_name' => 'require', 'vip_password' => 'require'],
		        'reg'  =>    ['vip_name','vip_password','vip_phone'],
		    ];








 }