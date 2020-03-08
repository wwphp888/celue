<?php
/*
在线支付基础配置
*/
//----------------------------------------------------------------

$congig = array(
   //收款APPID号
   "appid"=>"3114081111",
   
   //对应的APPKEY密匙
   "appkey"=>"92e8e5f9d7a15777832c5f84b3fbc6d0",
   
   //网关连接地址 一般不做修改
 //  "server"=>"http://39.98.91.48/",   //注意：最后要加斜杠 /
   	"server"=>"http://yunpay.waa.cn/",
   //支付成功后的跳转地址
   "reurl"=>"http://".$_SERVER['HTTP_HOST']."/",
   //默认当前域名,可根据自己的需求自己开发
   //如果跳转需要带参数 请在AJAX页面自行组合并添加，这个只是一个返回效果并无数据返回
   //请用户不要误认为是异步数据通知的链接
   
   //获取客户IP(必须)
   "uip"=>getIp(),
   
   //模板提示支付帮助 1提示 0不提示
   "helpts"=>1 ,
   
   "alipayh5"=>1 //是否开启自动生成二维码，开启后云端上传的二维码将失效
   
);
//----------------------------------------------------------------




//获取客户端IP地址
function getIp()
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
function urlparams($params){
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



/* PHP CURL HTTPS POST */
function curl_post_https($url,$data){ // 模拟提交数据函数
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

function parseurl($url="")
{
$url = rawurlencode(mb_convert_encoding($url, 'gb2312', 'utf-8'));
$a = array("%3A", "%2F", "%40");
$b = array(":", "/", "@");
$url = str_replace($a, $b, $url);
return $url;
}
?>