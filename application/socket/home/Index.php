<?php

namespace app\socket\home;

use app\index\controller\Home;
use think\Db;
use util\Tree;


/**
* 
*/
class Index extends Home
{
	
	public function index(){
		var_dump("index");
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Server_Login","rid":"1","para":{"LoginID" : "test","LoginPW" : "admin001","Encode" : 0}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 8192);
	}

	public function index2(){
		var_dump("index2");
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Trade_Init","rid":"1","para":{"Broker" : 32,"Net" : 0,"Server" : 2,"ClientVer" : "","TryConn" : 3,"Core" : 0}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 8192);
	}

	public function index3(){
		var_dump("index3");
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Trade_Login","rid":"1","para":{ "Server" : 2, "AccountMode" : 8, "Broker" : 81, "CreditAccount" : 0, "DeptID" : "1", "Encode" : 0, "ReportSuccess" : 1000, "TryConn" : 3, "IP" : "220.178.30.162", "LoginID" : "650012900", "LoginPW" : "218810", "Port" : 7708, "TradeID" : "", "CommPW" : ""}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 8192);
	}

	public function index4(){
		var_dump("index4");
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5677', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Trade_QueryData","rid":"5","para":{"JsonType" : 1,"QueryType" : 1}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 8192);
	}

	public function index5(){
		var_dump("index5");
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5677', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Trade_QueryQuote","rid":"3","para":{"Codes" : "000001","JsonType" : 1,"Server" : 1}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 1811292);
	}

	public function index6(){
		//$data = '{"event":"Trade_OrderOKEvent","rid":0,"oid":"1700","cid":"0","data":{"成交时间":"13:39:07","证券代码":"601988","证券名称":"中国银行","买卖标志":"1","买卖标志1":"卖出","成交价格":"3.63","成交数量":100,"成交金额":"363.00","成交编号":"5687918","委托编号":"1700","股东代码":"A342702456","成交类型":"","操作数据":"","保留信息":""}}';
		$datas = json_decode($data,true);
		$res = sellgupiao($datas,41);
		var_dump($res);
	}
	public function index7(){
		$res = plugin_action('Price', 'Price', 'updateorder');
		var_dump("111");
	}
  public function index8(){
		
		// 建立socket连接到内部推送端口
		$client = stream_socket_client('tcp://127.0.0.1:5677', $errno, $errmsg, 1);
		// 推送的数据，包含uid字段，表示是给这个uid推送
		//$data = array('uid'=>'uid1', 'percent'=>'88%233333');
		// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
		//fwrite($client, json_encode($data)."\n");
		 $one = '{"req":"Server_Login","rid":"1001","para":{"LoginID" : "yingchuang","LoginPW" : "admin001","Encode" : 0}}}';
		 fwrite($client, $one."\n");
		// 读取推送结果
		echo fread($client, 1811292);
	}
  public function index9(){
  	$parm = '{"req":"Trade_CommitOrder","rid":"1007","para":[ { "Code" : "601288", "Count" : 100000, "EType" : 2, "OType" : 1, "PType" : 1, "Price" : "3.62" } ] }';
    $res = get_socket_info($parm);
    var_dump($res);
  }

} 