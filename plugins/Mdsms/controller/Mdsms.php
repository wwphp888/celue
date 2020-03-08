<?php

namespace plugins\Mdsms\controller;

use app\common\builder\ZBuilder;
use app\common\controller\Common;
use think\Db;
/**
 * 插件后台管理控制器
 * @package plugins\Mdsms\controller
 */
class Mdsms extends Common
{
    public function sendsms($phone,$code,$msg){
        header("Content-Type: text/html; charset=UTF-8");
            $config = plugin_config('Mdsms');
            $flag = 0; 
            $params='';
                    //要post的数据 
            $argv = array( 
                'sn'=>trim($config['username']), ////替换成您自己的序列号
                'pwd'=>strtoupper(md5(trim($config['username']).trim($config['password']))), //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
                'mobile'=>$phone,//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
                'content'=>$msg.$config['tip'],//iconv( "GB2312", "gb2312//IGNORE" ,'您好测试短信[XXX公司]'),//'您好测试,短信测试[签名]',//短信内容
                'ext'=>'',      
                'stime'=>'',//定时时间 格式为2011-6-29 11:09:21
                'msgfmt'=>'',
                'rrid'=>''
            ); 
            $begin = time()-3600;
            $end = time();
            $maps['phone'] = $phone;
            $maps['create_time'] = array("between",$begin,$end);
            $check = Db::name("mdsms")->where($maps)->count("id");
            if($check >= 10){
                return ['status'=>1,'message'=>'发送太过频繁,稍后再试'];
            }
            $insert = [
                'phone' => $phone,
                'code' => $code,
                'info' => $argv['content'],
                'create_time' => time(),
                'status' => 0

            ];
            //构造要post的字符串 
            //echo $argv['content'];
            foreach ($argv as $key=>$value) { 
                if ($flag!=0) { 
                    $params .= "&"; 
                    $flag = 1; 
                } 
                $params.= $key."="; $params.= urlencode($value);// urlencode($value); 
                $flag = 1; 
            } 
            $length = strlen($params); 
            //创建socket连接 
            $fp = fsockopen("sdk.entinfo.cn",8061,$errno,$errstr,10) or exit($errstr."--->".$errno); 
            //构造post请求的头 
            $header = "POST /webservice.asmx/mdsmssend HTTP/1.1\r\n"; 
            $header .= "Host:sdk.entinfo.cn\r\n"; 
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
            $header .= "Content-Length: ".$length."\r\n"; 
            $header .= "Connection: Close\r\n\r\n"; 
            //添加post的字符串 
            $header .= $params."\r\n"; 
            //发送post的数据
            //echo $header;
            //exit;
            fputs($fp,$header); 
            $inheader = 1; 
            while (!feof($fp)) { 
                $line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据 
                if ($inheader && ($line == "\n" || $line == "\r\n")) { 
                    $inheader = 0; 
                } 
                if ($inheader == 0) { 
                    // echo $line; 
                } 
            } 
            //<string xmlns="http://tempuri.org/">-5</string>
            $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
            $line=str_replace("</string>","",$line);
            $result=explode("-",$line);
           
            if(count($result)>1){
                 $insert_add = Db::name("mdsms")->insert($insert);
                return ['status'=>3,'message'=>'发送失败'];

            }else{
                $insert['status'] = 1;
                $insert_add = Db::name("mdsms")->insert($insert);
                 return ['status'=>2,'message'=>'发送成功'];
            }

    }

    public function checkphonecode($phone,$code){

        $check = Db::name("mdsms")->where("phone = '{$phone}'")->order("id DESC")->find();
        if(!is_array($check) || !isset($check['id'])){
             return ['status'=>1,'message'=>'验证码不正确,重新发送'];
        }
        if($check['code'] != $code){
            return ['status'=>1,'message'=>'验证码不正确,重新输入'];
        }
        if($check['create_time'] < time()-900){
             return ['status'=>1,'message'=>'验证码失效,重新发送'];
        }
        
        if($check['code'] == $code){
            return ['status'=>2,'message'=>'验证码正确'];
        }

    }
     
          
    
}