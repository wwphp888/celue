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
use think\Db;
use think\Request;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Notify extends Home
{
	
    public function mifengnotify(){
      //var_dump("11");
      $post = request()->param();
     //file_put_contents("return_1.txt", print_r($post,true));

    /* $post = array(
          'code' => '200',
          'data' => '{"merchant_order_sn":"15537423793478","order_sn":"201903281106208897774609","total":10,"attach":"123|909"}',
          'msg' => '成功',
          'sign' => 'B38F3E72B7CDDFF734D279D28ED22CE7',
     );*/

      $newkey = "de3db10564368b76c1b13a38ca38b885";
      $data = json_decode($post['data'],true);
      //var_dump($data);
      $sign = $this->getsign($post,$newkey);
      if($sign == $post['sign'] && $post['code'] == '200'){
        $this->payDone($data['merchant_order_sn'],$data['order_sn']);
        echo "success";
      }


    }
  public function fuyounotify(){
  	$html =  file_get_contents('php://input');
    error_log(print_r($html,1),3,'fuyou.log');
   // $html = 'MCHNTORDERID=15555573273444&SIGN=b47b0c388a8c0b9a66fdae6b0b00da00&MCHNTCD=0001000F2254929&BANKCARD=6217002340022586640&VERSION=2.0&RESPONSECODE=0000&ORDERID=002014725444&RESPONSEMSG=%E6%88%90%E5%8A%9F&AMT=100&TYPE=10';
   // $html ='MCHNTORDERID=15555720286140&SIGN=43fb45e9094f66ff106c82def7a609a0&MCHNTCD=0001000F2254929&BANKCARD=6217002340022586640&VERSION=2.0&RESPONSECODE=0000&ORDERID=002015134417&RESPONSEMSG=成功&AMT=100&TYPE=10';
    parse_str($html,$result);
    
    $sign = md5($result['TYPE']."|".$result['VERSION']."|".$result['RESPONSECODE']."|".$result['MCHNTCD']."|".$result['MCHNTORDERID']."|".$result['ORDERID']."|".$result['AMT']."|".$result['BANKCARD']."|gcpguo0qso5eflnm9lb9m30j5m7e5sfx");
  	if($result['SIGN']==$sign&&$result['RESPONSECODE']=='0000'){
    	  $this->payDone($result['MCHNTORDERID'],$result['ORDERID']);
    	 echo "success";
    }
	/*$arr = explode('&', $html);
    error_log(print_r($_POST,1),3,'fuyou2.log');
	error_log(print_r($arr,1),3,'fuyou.log');*/
  
  
  }
  public function td28_notify(){
  	//error_log(print_r($_POST,1),3,'28.log');
    error_log(print_r(file_get_contents("php://input"),1),3,'282.log');
   
  		$data = file_get_contents("php://input");
   		$data= json_decode($data,true);
    	$sign = '';
    	ksort($data);
  		foreach($data as $key=>$value){
        	if($key!='sign'&&$value!=''){
            
          $sign.=$key.'='.$value.'&';  
            
            }
        
        }
    	$sign = strtoupper(md5($sign.'key=gmGKwsetBxm8iXjP2pIX8H5Yv1ZzSrra'));
    if($sign==$data['sign']){
    
    		$this->payDone($data['pay_order_id'],$data['pay_order_id']);
      		echo 'success ';
    
    }else{
    	echo 'fail ';
    }
  }
  public function fupc_notify(){
  	
  	$mchnt_cd = $_POST['mchnt_cd'];
    $order_id = $_POST['order_id'];
    $order_date = $_POST['order_date'];
    $order_amt = $_POST['order_amt'];
    $order_st = $_POST['order_st'];
    $order_pay_code = isset($_POST['order_pay_code']) ? $_POST['order_pay_code'] : '';
    $order_pay_error = isset($_POST['order_pay_error']) ? $_POST['order_pay_error'] : '';
    $user_id = $_POST['user_id'];
    $fy_ssn = $_POST['fy_ssn'];
    $card_no = $_POST['card_no'];
    $RSA = $_POST['RSA'];

    $fy_date = $order_date;
    $data = $mchnt_cd . '|' . $user_id . '|' . $order_id . '|' . $order_amt . '|' . $fy_date . '|' . $card_no . '|' . $fy_ssn;

    $dataGBK = iconv('UTF-8', 'GBK', $data);

    // 测试用公钥，在正式环境时，更换为正式公钥
    $rsaKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCn26E6VU4mVfUlsaScZDuPyYTSszoFCS7ctk6K6kO4y6xZrr VSoGhrd6ej1PXa421uqvDEpmrrnZBaJO0y7S/6niWNzwZQ5ajWo929ZH0HrqsD4DENUEyBTj8U9etnxx7J1wZFtPzgHd3FrUSj1RVjpy5QA40 ls29KD2DhJU/oFwIDAQAB';
    $pemKey = chunk_split($rsaKey, 64, "\n");
    $pem = "-----BEGIN PUBLIC KEY-----\n" . $pemKey . "-----END PUBLIC KEY-----\n";
    $pubKey = openssl_pkey_get_public($pem);

    $ret = openssl_verify($dataGBK, base64_decode($RSA), $pubKey, OPENSSL_ALGO_MD5);
    if ($ret==1) { // 说明验签成功
    echo 'SUC';
    
    // 对订单进行处理的逻辑
    if ($order_st=='11' && $order_pay_code=='0000') {
        // 支付成功，订单处理为成功，对用户账号加钱等操作
			 $this->payDone($order_id,$fy_ssn);
        } else {
            // 支付未成功，订单处理为失败
        }

    } else {
        echo 'FAIL';
    }
  
  }
  public function alipay_notify(){
  		error_log(print_r($_POST,1),3,'alipay.log');
    	/*$_POST=array(
        	    'ddh' => '2019050522001494131032976185',
    'money' => '1.00',
    'name' => '',
    'key' => '92e8e5f9d7a15777832c5f84b3fbc6d0',
    'paytime' => '2019/5/5 17:30:34',
    'lb' => '1',
    'type' => ''
        
        
        );*/
  		      $ddh = $_POST['ddh']; //支付宝,微信，QQ钱包 订单号
       
      $key = $_POST['key']; //APPKEY验证，也可以使用签名在软件中开启
       
      $name = $_POST['name']; //备注信息  接收网关data 参数  支付订单号
       
      $lb = $_POST['lb']; //分类 =1 支付宝 =2财付通 =3 微信
       
      $money = $_POST['money'];//金额
         
      $paytime = $_POST['paytime'];//充值时间
       
	  $key2 = '92e8e5f9d7a15777832c5f84b3fbc6d0';//APPKEY 和云端和软件上面保持一致 
	  
	  //使用签名，如果使用签名用 $sing对比  需要在软件配置中  使用签名 钩上
	  $sing =md5('ddh='.$ddh.'&name='.$name.'&money='.$money.'&key='.$key2.'');
  
  		 if($key==$key2 &&  $lb==1){//直接对比appkey是否正确
	  //if($key==$sing){//使用签名对比key是否正确
		  //判断支付来源
           	 $this->payDone($name,$ddh);
           	echo "ok";
         }else{
         	echo 'appkey error'; 
         }
  
  }
private function payDone($order,$order_sn){
      $check = Db::name("vip_recharge")->where("recharge_order",$order)->find();
  //	print_r($check);exit;
      if(!isset($check['id'])){
        return false;
      }
      if($check['recharge_status'] > 0){
        return false;
      }
      $data['recharge_status'] = 1;
      $data['recharge_order_sn'] = $order_sn;
      $res = Db::name("vip_recharge")->where("id",$check['id'])->update($data);
      if($res){
        money_log($check['recharge_amount'],$check['recharge_vip'],1,'线上充值成功，订单号:'.$order);
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
            if(!empty($value)&&$value !==''&&$key !=='sign'){
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
 
}
