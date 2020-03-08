<?php

namespace app\vip\home;


use app\vip\home\Index;
use think\Db;
use think\Request;

/**
 * 
 */
class Recharge extends Index
{
	public function index(){
		$chargelist = Db::name('payoff_conf')->where("status",1)->select();
		$this->assign("chargelist",$chargelist);


		 return $this->fetch(); // 渲染模板
	}

	public function log(){

		$map['recharge_vip']= $this->uid;
		
		if(input('time')){


			$map['recharge_time'] =array('gt',date("Y-m-d",strtotime("-".input('time')." month")));
		}
		
		$status_name = array(0=>'待审核',1=>'成功',2=>'失败',3=>'成功');
		$recharge_type = array(1=>'线上支付',2=>'线下支付');
		
		$list = Db::name('vip_recharge')->where($map)->order("id desc")->paginate(10)->each(function($item,$key)use($status_name,$recharge_type){
			$item['status_name'] = $status_name[$item['recharge_status']];
			$item['type_name'] = $recharge_type[$item['recharge_type']];
			return $item;
		});

 		$this->assign('list', $list);//单独提取分页出来
		$this->assign('page', $list->render());//单独提取分页出来

		 return $this->fetch(); // 渲染模板


	}

	public function payonline(){
		$params = request()->param();
		if($params['payofftype'] =='' || empty($params['payofftype'])){
			return json(['status'=>0,'message'=>'请选择支付方式']);	
		}
		if($params['money'] < 1){
			return json(['status'=>0,'message'=>'充值金额不能低于1元']);	
		}
		if($params['payofftype'] == 'quickpay'){
			///////////////////////蜜蜂支付/////////////////////////////
			$newdata['merchant_no'] = "100113"; //商户号
			$newdata['merchant_order_sn'] = time().rand(1000,9999);
			$newdata['type'] = $params['payofftype'];
			$newdata['total'] = $params['money'];
			$newdata['body'] = '1';
			$dqurl = Request::instance();
			$newdata['url_notify'] = $dqurl->domain()."/index/notify/mifengnotify";
			$newdata['attach'] = '123|909';
			$newkey = "de3db10564368b76c1b13a38ca38b885";
			$newdata['sign'] = $this->getsign($newdata,$newkey);
	
			$this->createnid($newdata['total'],$newdata['merchant_order_sn']);
			//var_dump($newdata);
			$res = $this->submitdata("https://a.goldbeepay.com/orderIndex",$newdata);
			//var_dump($res);
			$resjson = json_decode($res,true);
			if(isset($resjson['code'])){
				print_r(json_decode($res,true)['msg']);
			}else{
				echo $res;
			}
			
        }
      /***支付宝***/
      if($params['payofftype'] == 'alipaywap'){
      	$appid = '3114081111';
        $appkey='92e8e5f9d7a15777832c5f84b3fbc6d0';
        	$yundata = array(
                     "appid"  => $appid,
                     "data"   =>time().rand(1000,9999),//网站订单号/或者账号
                     "money"  => number_format($params['money'],2,".",""),//注意金额一定要格式化否则token会出现错误
                     "type"   => (int)1,
                     "uip"    => $this->getIp(),
                  );
        	$token = array(
                    "appid"  =>  $appid,//APPID号码
                    "data"   =>  $yundata["data"],//数据单号
                    "money"  =>  $yundata["money"],//金额
                    "type"   =>  $yundata["type"],//类别
                    "uip"    =>  $yundata['uip'],//客户IP
                    "appkey" => $appkey//appkey密匙
                  );
        $token = md5($this->urlparams($token));
		$postdata = $this->urlparams($yundata).'&token='.$token;
        //构建请求二维码

		 $order_data = base64_encode($yundata["data"].','.$yundata["money"]);//将数据进行base64编码
   		  $qrcode = 'http://'.$_SERVER['HTTP_HOST'].'/index/Alipay?data='.$order_data.'&uid='.$this->uid;//本地自动生成二维码地址
  		 $sdata = array('state'=>1,'qrcode'=>$qrcode,'youorder'=>$yundata["data"],'data'=>$yundata["data"],'money'=>$yundata["money"],'times'=>time() + 300,'orderstatus'=>0,'text'=>10089); //本地生成二维码可手动伪造JSON数据
         $state = $sdata["state"];//状态 1 ok   0有错误

        if(!$state){
            exit('异常'.$sdata["text"]);
        }

        $qrcode = $sdata["qrcode"];//二维码

        $times =  $sdata["times"] - time(); //有效时间减去当前时间 保留一分钟减去60秒

        $moneys = $sdata["money"];//实际付款金额

        $orderstatus =$sdata["orderstatus"];//付款状态 1ok  0等待付款

        $data =$sdata["data"];//传递的订单号

        $order =$sdata["order"];//云端分配的唯一订单号 通过这个订单号查询状态

        //

		$logo = '/static/home/js/template/Image/zfb.png';
		$title = '支付宝';	
		$text =  '支付宝扫一扫付款（手机上可以直接启动APP，或者截图相册识别）';
		$tishi = '<div style="position:relative;width:300px;height:341px;margin:0 auto;border:1px solid #e4e3e3"><img src="/static/home/js/template/Image/zfbbg.png" alt="" /><div style="position:absolute;width:100px;height:100px;z-indent:2;left:35;top:210;font-size:48px;color:#F00">'.$moneys.'</div></div>';
      //html页面
		$this->createnid($moneys,$data);
        return $this->fetch('Alipay',['title'=>$title,'logo'=>$logo,'moneys'=>$moneys,'text'=>$text,'data'=>$data,'times'=>$times,'qrcode'=>$qrcode,'order'=>$order,'tishi'=>$tishi]); 	
      }
      if($params['payofftype'] == 'fuyou'){
      	//支付校验
        /*$order_id = time().rand(1000,9999); // 商户订单号
        $order_amt = $params['money']*100; // 交易金额，以分为单位
        $vip_info=Db::name('vip')->field('vip_idcard,vip_realname')->where('id',$this->uid)->find();
          //print_r($vip_info);
          	if(empty($vip_info['vip_idcard'])||empty($vip_info['vip_realname'])){
            
            	$this->error('您还没有实名认证,请先认证');
          }
        $card_no = $params['bankcard'];
        $cardholder_name = $vip_info['vip_realname'];
        $cert_no = $vip_info['vip_idcard'];

        $_page_notify_url = "http://".$_SERVER['HTTP_HOST']; // 页面跳转URL
        $_back_notify_url = "http://".$_SERVER['HTTP_HOST']."/index/notify/fupc_notify";// 后台通知URL

        $_mchnt_cd = '0001000F2254929'; // 测试商户代码，在正式环境时，更换为正式的商户号
        $cert_type = 0;

        $user_id = $this->uid; // 用户id

        $data = $_mchnt_cd . '|' . $user_id . '|' . $order_id . '|' . $order_amt . '|' . $card_no . '|' . $cardholder_name . '|' . $cert_type . '|'  . $cert_no . '|' . $_page_notify_url . '|' . $_back_notify_url;

        $dataGBK = iconv('UTF-8', 'GBK', $data);

        // rsa私钥，在正式环境时，更换为正式的私钥
        $rsaKey = 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAI1WZkYW2rL3PEzHnsZo12jCb04MUwc43IVNST8bUkEmgSCLOLxKsnEvcAWJFBZTRxOCMVrrCBKxmWWdfcQveYNBKmydf5CE3CZ4zPmSPCVn26fmXiYFPlEGkaXjidgmSn6WPmkS70PJ0+HCF3jVZUWYETGrjTLcLgMVUkCEAfE7AgMBAAECgYBE3wghOTvCn4ULqO4uoqHs02onbwv6ZfPXJQz/KlIPmzKq6AxyfDetvb7pg519L4Ff+T4RnpuleFNWV8MUGUotNTaO5trTiwr9Dc6/nN5hI92k//jUfY7J5tuUenDj3CJtVAESpaeeFST5PWIpEZ/uWkoCvu8F9F3DgSM8CWK6+QJBAOhX5Pyf62rOqFA7z+hJbRjYwpPqnPmRtEV2yiCTKJGMhHItLmb8VU0xkDKY0y/dSm4lwE8cpO/sLKj/2U9SsQcCQQCbumhq7ylIht/lBf8oFZtT60POGIfhUGGc7nxRzYUUGDBLoejsfnL3QjltvCT0daHL5NAlZMmRpLQVCOBb4tUtAkEA3faCEvlv/LR6xVOutnnXGKKTmJ3M4vtYXfgy91W+rvWv3ifdqZMsprdPy5aGQrbEkV/NTYbO50oYDEeHwij8ZwJAanNnM0ne/4vq+sQ5oi366seUpwpPwC5RO5QueUCy6oSKZvj7nsXlUq37Uc7duBm9CwKTYixeOWfMDeDudQE+1QJACtQ8bhdzDq2ArhnsH+bRspu1NeKz5dpQh459cge0SM8qq/RNyqtQKT1RHsckwJobuOT8lz368YaKlPmUyVyuUQ==';
        $pemKey = chunk_split($rsaKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pemKey . "-----END PRIVATE KEY-----\n";
        $priKey = openssl_pkey_get_private($pem);

        openssl_sign($dataGBK, $encrypted, $priKey, OPENSSL_ALGO_MD5); // 对数据进行签名
        $RSA = base64_encode($encrypted);
      	$newdata = [
        	'mchnt_cd'=>$_mchnt_cd,
          	'order_id'=>$order_id,
          	'order_amt'=>$order_amt,
          	'user_type'=>'0',
          	'page_notify_url'=>$_page_notify_url,
          	'back_notify_url'=>$_back_notify_url,
          	'card_no'=>$card_no,
          	'cert_type'=>'0',
          	'cert_no'=>$cert_no,
          	'cardholder_name'=>$cardholder_name,
          	'user_id'=>$user_id,
          	'RSA'=>$RSA
        
        ];
        	$this->createnid($params['money'],$order_id);
      	$this->create('https://pay.fuioupay.com/dirPayGate.do',$newdata);*/
      	
        //start
         $vip_info=Db::name('vip')->field('vip_idcard,vip_realname')->where('id',$this->uid)->find();
          //print_r($vip_info);
          	if(empty($vip_info['vip_idcard'])||empty($vip_info['vip_realname'])){
            
            	$this->error('您还没有实名认证,请先认证');
          }
        $sbdata = [
        	'partner'=>'321129',
        	'channelid'=>'28',
          	'orderno'=> time().rand(1000,9999),
         	 'amount'=>$params['money'],
          	'notifyurl'=>"http://".$_SERVER['HTTP_HOST']."/index/notify/td28_notify",
          	'return_url'=>"http://".$_SERVER['HTTP_HOST'],
        	'card_no'=>$params['bankcard'],
          	'cardholder_name'=>$vip_info['vip_realname'],
          	'cert_no'=> $vip_info['vip_idcard'],
        
        ];
        $signstring ='';
        ksort($sbdata);
      	foreach($sbdata as $key=>$value){
			if($value!=''){
			$signstring.=$key.'='.$value.'&';	
              }

        }
          $sbdata['sign'] = strtoupper(md5($signstring.'key=gmGKwsetBxm8iXjP2pIX8H5Yv1ZzSrra'));
        $this->createnid($params['money'],$sbdata['orderno']);
      	$this->create('http://www.xcwhwh.cn:39110/api/pay/order_pay',$sbdata);
        // $sbdata['sign'] = md5(substr($signstring,0,-1).'gmGKwsetBxm8iXjP2pIX8H5Yv1ZzSrra');
      	 //print_r($signstring);exit;
      }
 
		//var_dump($params);
	}
	//*******************************
  
  	    public static  function encryptForDES($input,$key)   
    {         
       $size = mcrypt_get_block_size('des','ecb');  
       $input = self::pkcs5_pad($input, $size);  
       $td = mcrypt_module_open('des', '', 'ecb', '');  
       $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);  
       @mcrypt_generic_init($td, $key, $iv);  
       $data = mcrypt_generic($td, $input);  
       mcrypt_generic_deinit($td);  
       mcrypt_module_close($td);  
       $data = base64_encode($data);  
       return $data;  
    }   
    
             
    public static  function pkcs5_pad ($text, $blocksize)   
    {         
       $pad = $blocksize - (strlen($text) % $blocksize);  
       return $text . str_repeat(chr($pad), $pad);  
    } 
        
    public static  function pkcs5_unpad($text)   
    {         
       $pad = ord($text{strlen($text)-1});  
       if ($pad > strlen($text))  
       {  
           return false;  
       }  
       if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)  
       {  
          return false;  
       }  
       return substr($text, 0, -1 * $pad);  
    }  
  
  //*****************************************


	public function payoff(){
		$params = request()->param();
		if($params['payofftype'] < 1 || empty($params['payofftype'])){
			return json(['status'=>0,'message'=>'请选择支付方式']);	
		}
		if($params['payoffmoney'] < 1){
			return json(['status'=>0,'message'=>'充值金额不能低于1元']);	
		}
		if($params['payoffinfo'] == ''){
			return json(['status'=>0,'message'=>'请填写充值备注信息']);	
		}
		$chargeconf = Db::name("payoff_conf")->where("id",$params['payofftype'])->find();
		$data['recharge_amount'] = $params['payoffmoney'];
		$data['recharge_status'] = 0;
		$data['recharge_vip'] = $this->uid;
		$data['recharge_type'] = 2;
		$data['recharge_info'] = $params['payoffinfo'];
		$data['recharge_title'] = $chargeconf['title'];
		$data['recharge_bankname'] = $chargeconf['bankname'];
		$data['recharge_number'] = $chargeconf['number'];
		$data['recharge_time'] = time();
		$res = Db::name("vip_recharge")->insert($data);
		if($res){
			return json(['status'=>1,'message'=>'提交申请成功']);	
		}else{
			return json(['status'=>0,'message'=>'提交申请失败']);	
		}


	}
	private function createnid($money,$order){
		$data['recharge_amount'] = $money;
		$data['recharge_status'] = 0;
		$data['recharge_vip'] = $this->uid;
		$data['recharge_type'] = 1;
		$data['recharge_order'] = $order;
		$data['recharge_time'] = time();
		$res = Db::name("vip_recharge")->insert($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}


	private function getsign($data,$newkey,$type='ksort',$nul='1'){
		switch ($type) {
			case 'ksort':
				ksort($data);
				$pinjie = '';
				foreach ($data as $key => $value) {
				    if(!empty($value)&&$value !==''){
				         $pinjie .= $key."=".$value."&";
				    }
				   
				}
				$pinjie = substr($pinjie,0,strlen($pinjie)-1).$newkey;
				//var_dump($pinjie);
				$sign = strtoupper(md5($pinjie));
				break;
		
		}
		return $sign;
	}
  public function curl_post_https($url,$data){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}
  //获取客户端IP地址
 public function getIp()
  { //取IP函数
      static $realip;
      if (isset($_SERVER)) {
          if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
              $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
          } else {
              $realip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
          }
      } else {
          if (getenv('HTTP_X_FORWARDED_FOR')) {
              $realip = getenv('HTTP_X_FORWARDED_FOR');
          } else {
              $realip = getenv('HTTP_CLIENT_IP') ? getenv('HTTP_CLIENT_IP') : getenv('REMOTE_ADDR');
          }
      }
      $realip=explode(",",$realip);

      return $realip[0];
  }
   //数组拼接为url参数形式
public function urlparams($params){
    $sign = '';
    foreach ($params AS $key => $val) {
        if ($val == '') continue;
        if ($key != 'sign') {
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val"; //拼接为url参数形式
        }
    }
    return $sign;
}

	private function submitdata($url,$data){
		$options = array(
		    'http' => array(
		        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method' => 'POST',
		        'content' => http_build_query($data)
		    ),
		   /* "ssl"=>array(
		                "verify_peer"=>false,
		                "verify_peer_name"=>false,
		            )*/
		);
		//var_dump($options);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
	private function create($submitUrl,$data){
		$inputstr = "";
		foreach($data as $key=>$v){
			$inputstr .= '
		<input type="hidden"  id="'.$key.'" name="'.$key.'" value=\''.$v.'\'"/>
		';
		}
		
		$form = '
		<form action="'.$submitUrl.'" name="pay" id="pay" method="POST">
';
		$form.=	$inputstr;
		$form.=	'
</form>
		';
		
		$html = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>请不要关闭页面,支付跳转中.....</title>
        </head>
<body>
        ';
        $html.=	$form;
        $html.=	'
        <script type="text/javascript">
			document.getElementById("pay").submit();
		</script>
        ';
        $html.= '
        </body>
</html>
		';
				 
		//Mheader('utf-8');
		echo $html;
		exit;
	}
}