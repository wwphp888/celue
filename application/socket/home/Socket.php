<?php

namespace app\socket\home;

use think\worker\Server;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\AsyncTcpConnection;
use think\Cache;
use Workerman\MySQL\Connection;
class Socket extends Server
{
    protected $socket = 'websocket://127.0.0.1:2347';
     protected $processes = 1;
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    
    public function onMessage($connection, $data)
    {
        
        $connection->send('我收到你的信息了'.$data);
        error_log(print_r($connection,1),3,'woker.log');


    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        $connection->send('????');
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        
        global $db;
        //////////////////////////////////////////
        $dburl = '127.0.0.1'; //数据库地址
        $dbname = 'celue';//数据库名称
        $dbuser = 'celue'; //数据库用户名
        $dbpass = '30b83191c317f568';//数据库密码
        $dbport = '3306'; //端口号
        //////////////////////////////////////

        $db = new Connection($dburl, $dbport, $dbuser, $dbpass, $dbname);

        /////////////////实盘参数//////////////////
        global $shipan_api;//实盘链接
        global $text_port; //本地端口
        global $login_arr; 
        global $textcontent;
        $shipan_api = '47.105.60.177:888'; //实盘链接
        $text_port = '5677'; 

        $login_arr=array(
            'user'=>'test',        #交易用户名
            'password'=>'admin001',   #交易同密码
            'broker'=>'81',         #证券编号
            'core' => '0'       #内核版本
        );
        /////////////////实盘参数/////////////////

        $inner_text_worker = new Worker('Text://127.0.0.1:'.$text_port);
      
      
        $inner_text_worker->onMessage = function($connection, $buffer)use($worker)
        {
          global $textcontent;
           $jsbuffer = json_decode($buffer,true);
           if(isset($jsbuffer['rid'])){
             $textcontent[$jsbuffer['rid']]=$connection;
           }
            if($worker->uidConnections){


                //echo 111;
               // error_log(print_r($worker->uidConnections,1),3,'mmm.log');
                $worker->uidConnections->send($buffer);

            }     
      


           
        };
        $worker->uidConnections = $inner_text_worker;
        $inner_text_worker->listen();
        global $shipan_api;
        $con = new AsyncTcpConnection('ws://'.$shipan_api.'?rid=DFC76286A2F293472D6166F72CA58EAB&flag=0');

                   
        $con->onConnect = function($conn){
            global $login_arr;
            global $db;
            Timer::add(30, function()use($conn){
                 $ping_msg = '{"req":"Trade_CheckStatus","rid":"2001","para":{"Server" : 2}}';
                 error_log(date('Y-m-d H:i:s').PHP_EOL.print_r($ping_msg,1),3,'ping.log');
                 $conn->send($ping_msg);
            
            });
            //发送普通行情初始化请求
            Cache::rm('Trade_Init_list'); 
            Cache::rm('Trade_Init_level'); 
            Cache::rm('Trade_Init_conf'); 
            Cache::rm('Trade_Login_status'); 
            $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" :'.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
            $conn->send($msg);
           
             //登录交易通系统请求 rid 1001开始
            $server_msg = '{"req":"Server_Login","rid":"1001","para":{"LoginID" : "'.$login_arr['user'].'","LoginPW" : "'.$login_arr['password'].'","Encode" : 0}}';
            $conn->send($server_msg);
            // error_log(PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.print_r($msg,1),3,'toady.log');
           
           
       };
       $con->onClose = function($conn){
            echo date("Y-m-d H:i:s")."_onclose";
            $conn->reConnect(10);
       };
        $con->onMessage = function($con, $data)use($inner_text_worker){
            global $login_arr;
            global $db; 
            global $textcontent;
          // error_log(PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.print_r($data,1),3,'toady.log');
            $data = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0|\n|\r|\r\n|\t)/","",$data);
            $datas = json_decode($data,true);
            if(isset($datas['data']['ErrInfo'])){
                // error_log(date('Y-m-d H:i:s').PHP_EOL.print_r($data,1),3,'socketerror.log');
                //凡rid所在异常，此处均不处理
            $ridlist = array('0','1','2','1001','1002','1003','1007','1008');
                 if(isset($datas['event']) && !in_array($datas['rid'], $ridlist)){
                    //处理行情服务器异常断开
                    if($datas['event'] == 'Market_ServerErrEvent' || $datas['event'] == 'Market_LoginEvent'){
                        //重新发送普通行情初始化请求
                        $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                        $con->send($msg);
                    }elseif($datas['event'] == 'Server_Login'){
                         //重新登录交易通系统请求 rid 1001开始
                        $server_msg = '{"req":"Server_Login","rid":"1001","para":{"LoginID" : "'.$login_arr['user'].'","LoginPW" : "'.$login_arr['password'].'","Encode" : 0}}';
                        $conn->send($server_msg);
                    }
                 }
            }
          //  error_log(print_r($data,1),3,'2busqqqq.txt');
           // error_log(print_r($datas,1),3,"noti.log");
           // echo $data;
                switch ($datas['rid']) { //行情发送rid由1开始   交易服务器rid 1001开始
                    case '0':
                        if(isset($datas['event'])){
                            if($datas['event'] == 'Market_ServerErrEvent'){
                                //重新发送普通行情初始化请求
                                $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                                $con->send($msg);
                               
                            }
                            if(isset($datas['data']['ErrInfo']) && $datas['event'] == 'Market_LoginEvent'){
                                    $Trade_Init_list = Cache::get('Trade_Init_list');
                                    $Trade_Init_level = Cache::get('Trade_Init_level')+1;
                                    $Trade_Init_conf = Cache::get('Trade_Init_conf');
                                    //如果获取接点列表错误，就断开服务

                                    if(is_array($Trade_Init_list) && $Trade_Init_level < count($Trade_Init_list)){
                                        Cache::inc('Trade_Init_level');//行情接点自增
                                        echo $Trade_Init_list[$Trade_Init_level]['IP'];
                                         $msg = '{"req":"Trade_Login","rid":"2","para":{"IP" : "'.$Trade_Init_list[$Trade_Init_level]['IP'].'","Port" :'.$Trade_Init_list[$Trade_Init_level]['Port'].',"Server" : 1}}';
                                         $con->send($msg);
                                    }else{
                                       //重新发送普通行情初始化请求
                                        $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                                        $con->send($msg);
                                    }
                            }
                            if($datas['event'] == 'Trade_OrderOKEvent'){
                                    //$res = '{"event":"Trade_OrderOKEvent","rid":0,"oid":"1051","cid":"0","data":{"成交时间":"09:54:23","证券代码":"601988","证券名称":"中国银行","买卖标志":"0","买卖标志1":"买入","成交价格":"3.64","成交数量":100,"成交金额":"364.00","成交编号":"1910635","委托编号":"1051","股东代码":"A342702456","成交类型":"","操作数据":"","保留信息":""}}';
                                 // $info = $datas;
                                //标志为0为买入 1为卖出
                                if($datas['data']['买卖标志'] == '0'){
                                    // 执行SQL
                                 $insert_id = $db->insert('tsp_notify')->cols(array(
                                                                          'type'=>'买入',
                                                                          'info'=>$data,
                                                                          'add_time'=>time()))->query();
                                    // $buystatus = buygupiao($datas,$notifyid);
                                    #######################################################################
                                       //////////////////////买入异步处理//////////////////////////////////////////
                                          $map['trush_no'] = $datas['oid'];
                                          $map['gupiao_code'] = $datas['data']['证券代码'];

                                         // $check = Db::name("trade_order")->where($map)->find();
                                         // $check = $db->select("id")->from('tsp_trade_order')->where("trush_no = '{$map['trush_no']}' and gupiao_code = '{$map['gupiao_code']}'")->row();
                                          $check = $db->select("*")->from('tsp_trade_order')->where("trush_no = {$map['trush_no']} and gupiao_code = {$map['gupiao_code']}")->row();
                                          
                                          if(!is_array($check)){
                                            return false;
                                          }
                                          if($check['status'] > 1){
                                            return false;
                                          }
                                          
                                          if($check['deal_no'] != ''){
                                             $dede['deal_no'] = $check['deal_no']."|".$datas['data']['成交编号'];
                                          }else{
                                             $dede['deal_no'] = $datas['data']['成交编号'];
                                          }
                                          
                                          if($check['deal_number'] > 0){
                                             $dede['deal_number'] = $check['deal_number']+floatval($datas['data']['成交数量']);
                                          }else{
                                             $dede['deal_number'] = $datas['data']['成交数量'];
                                          }

                                          if($check['notify_id'] != ''){
                                            $dede['notify_id'] = $check['notify_id']."|".$insert_id;
                                          }else{
                                            $dede['notify_id'] = $insert_id;
                                          }
                                          $dede['deal_time'] = time();
                                          if($check['trush_number'] == $dede['deal_number']){
                                            $dede['status'] =2;//持仓中
                                          }
                                          $res = $db->update('tsp_trade_order')->cols($dede)->where("id={$check['id']}")->query();
                                          
                                          if($res){
                                            if($insert_id > 0){
                                                $db->update('tsp_notify')->cols(array('status'=>'1'))->where("id={$insert_id}")->query();
                                            }
                                            return true;
                                        }else{
                                            if($insert_id > 0){
                                                 $db->update('tsp_notify')->cols(array('status'=>'2'))->where("id={$insert_id}")->query();
                                            }
                                            return false;
                                        }
                                    ########################################################################  
                                }elseif($datas['data']['买卖标志'] == '1'){
                                    // 执行SQL
                                $insert_id = $db->insert('tsp_notify')->cols(array(
                                                                  'type'=>'卖出',
                                                                  'info'=>$data,
                                                                  'add_time'=>time()))->query();
                                     //$buystatus = sellgupiao($datas,$notifyid);
                                #############################################################################
                                     //委托编号
                                          $map['sell_trush_no'] = $datas['oid'];
                                          $map['gupiao_code'] = $datas['data']['证券代码'];
                                          $check = $db->select("*")->from('tsp_trade_order')->where("sell_trush_no = {$map['sell_trush_no']} and gupiao_code = {$map['gupiao_code']}")->row();
                                         
                                          if(!is_array($check)){
                                            return false;
                                          }
                                          if($check['status'] != '3'){
                                            return false;
                                          }
                                          if(strpos($check['sell_deal_no'],$datas['data']['成交编号'])!==false){
                                            return false;
                                          }
                                         
                                         $db->beginTrans();
                                          if($check['sell_deal_no'] != ''){

                                             $dede['sell_deal_no'] = $check['sell_deal_no']."|".$datas['data']['成交编号'];
                                          }else{
                                             $dede['sell_deal_no'] = $datas['data']['成交编号'];
                                          }
                                          
                                          if($check['sell_deal_number'] > 0){
                                             $dede['sell_deal_number'] = $check['sell_deal_number']+floatval($datas['data']['成交数量']);
                                          }else{
                                             $dede['sell_deal_number'] = $datas['data']['成交数量'];
                                          }
                                          //如果成交金额大于委托金额，则为重复通知
                                          if($dede['sell_deal_number'] > $check['sell_number']){
                                            $dede['sell_deal_number'] = $check['sell_number'];
                                          }

                                          if($check['sell_notify_id'] != ''){
                                            $dede['sell_notify_id'] = $check['sell_notify_id']."|".$insert_id;
                                          }else{
                                            $dede['sell_notify_id'] = $insert_id;
                                          }
                                          $dede['sell_deal_time'] = time();
                                          if($check['sell_number'] == $dede['sell_deal_number']){
                                             $dede['status'] = 4;//已平仓完成
                                             //返还信用金及收益
                                             $prifits = ($check['sell_price']-$check['trush_price'])*$dede['sell_deal_number'];
                                             if($prifits > 0){
                                                $dede['repay_creat_money'] = $check['credit_money'];
                                                $dede['repay_profits'] = $prifits;
                                             }else{
                                                $dede['repay_creat_money'] = $check['credit_money']+$prifits < 0 ?0:$check['credit_money']+$prifits;
                                                $dede['repay_profits'] = 0;
                                             }
                                          }else{
                                             $dede['repay_creat_money'] = 0;
                                             $dede['repay_profits'] = 0;
                                          }
                                         // $res = Db::name("trade_order")->where("id",$check['id'])->update($dede);
                                          $res = $db->update('tsp_trade_order')->cols($dede)->where("id={$check['id']}")->query();

                                         // $checkvip = Db::name("vip")->where("id",$check['user_id'])->find();
                                          $checkvip = $db->select("*")->from('tsp_vip')->where("id={$check['user_id']}")->row();

                                          $log['record_vip'] = $checkvip['id'];
                                          $log['type'] = 14;
                                          $log['record_affect'] = $dede['repay_creat_money']+$dede['repay_profits'];
                                          $log['record_money'] = $checkvip['vip_money']+$log['record_affect'];
                                          $log['record_info'] = "对".$check['gupiao_name']."进行卖出，返还信用金".$dede['repay_creat_money']."元+盈利".$dede['repay_profits']."元";
                                          $log['record_time'] = time();
                                          //$logres = Db::name("vip_record")->insert($log);
                                          $logres = $db->insert('tsp_vip_record')->cols($log)->query();

                                        //  $upres = Db::name('vip')->where('id',$checkvip['id'])->setInc('vip_money',$log['record_affect']);
                                         $upres = $db->update('tsp_vip')->cols(array('vip_money'=>$log['record_money']))->where("id={$checkvip['id']}")->query();
                                       
                                          if($res && $logres && $upres){
                                            $db->commitTrans();
                                            if($insert_id > 0){
                                                $db->update('tsp_notify')->cols(array('status'=>'1'))->where("id={$insert_id}")->query();
                                              
                                            }
                                            return true;
                                        }else{
                                           $db->rollBackTrans();
                                            if($insert_id > 0){
                                                //Db::name("notify")->where("id",$notifyid)->setField('status',2);
                                                $db->update('tsp_notify')->cols(array('status'=>'2'))->where("id={$insert_id}")->query();
                                            }
                                            return false;
                                        }   
                                #############################################################################

                                }else{
                                    // 执行SQL
                                 $insert_id = $db->insert('tsp_notify')->cols(array(
                                                                  'type'=>'未知标识',
                                                                  'info'=>$data,
                                                                  'add_time'=>time()))->query();
                                }
                                  
                                 
                            }
                        }
                        break;
                    case '1': //行情初始化获取接点列表 rid =1
                   // error_log(PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.print_r('返回rid 1',1),3,'toar.log');
                        if(isset($datas['time'])){
                             $Trade_Init_list = Cache::get('Trade_Init_list');
                          
                             if(!is_array($Trade_Init_list) || count($Trade_Init_list) < 1){
                                //优先获取自定义接点登录
                             
                               //error_log(PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.print_r(config('market'),1),3,'toar.log');
                                // $Trade_Init_list = Db::name("market_conf")->where("is_effect = 1")->select();
                               
                               $Trade_Init_list = config('market');
                                
                                 Cache::set('Trade_Init_list',$Trade_Init_list);
                                 Cache::set('Trade_Init_level',0);
                                 Cache::set('Trade_Init_conf',2); // 转换行情状态为2自定义接点
                             }else{
                                
                                $Trade_Init_level = Cache::get('Trade_Init_level');
                                $Trade_Init_conf = Cache::get('Trade_Init_conf');
                                if($Trade_Init_list < $Trade_Init_level && $Trade_Init_conf == '2'){
                                     Cache::set('Trade_Init_list',$datas['data']);
                                     Cache::set('Trade_Init_level',0);
                                     Cache::set('Trade_Init_conf',1); //设置行情状态 1为交易通行情接点 2为自定义行情接点
                                }
                             }
                              $Trade_Init_list = Cache::get('Trade_Init_list');
                          //error_log(print_r('取得缓存数组').PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.print_r($Trade_Init_list,1),3,'toar.log');
                              $Trade_Init_level = Cache::get('Trade_Init_level');
                              $Trade_Init_conf = Cache::get('Trade_Init_conf');
                              if(is_array($Trade_Init_list) && $Trade_Init_level < count($Trade_Init_list)){
                               
                                    $msg = '{"req":"Trade_Login","rid":"2","para":{"IP" : "'.$Trade_Init_list[$Trade_Init_level]['IP'].'","Port" :'.$Trade_Init_list[$Trade_Init_level]['Port'].',"Server" : 1}}';
                                    $con->send($msg);
                              }else{
                                 //重新发送普通行情初始化请求
                                
                                $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                                $con->send($msg);
                              }
                            
                            
                         }
                        break;

                    case '2': //获取普通行情接点登录结果 {"ret":0,"rid":2,"data":{"ID":0,"Wait":1}}
                         if(isset($datas['event'])){
                           // error_log(print_r($datas,1),3,"111133.log");
                            if(isset($datas['data']['ID'])&&!isset($datas['data']['ErrInfo']) && $datas['event'] == 'Market_LoginEvent'){
                                if($datas['data']['ID'] >0){
                                    Cache::set('Trade_Login_status',1); //登录成功
                                    echo "Trade_Login_success";
                                }else{
                                    $Trade_Init_list = Cache::get('Trade_Init_list');
                                    $Trade_Init_level = Cache::get('Trade_Init_level')+1;
                                    $Trade_Init_conf = Cache::get('Trade_Init_conf');
                                    //如果获取接点列表错误，就断开服务
                                    if(is_array($Trade_Init_list) && $Trade_Init_level < count($Trade_Init_list)){ //未进行数组下标最大值效验bug
                                        Cache::inc('Trade_Init_level');//行情接点自增
                                        echo $Trade_Init_list[$Trade_Init_level]['IP'];
                                         $msg = '{"req":"Trade_Login","rid":"2","para":{"IP" : "'.$Trade_Init_list[$Trade_Init_level]['IP'].'","Port" :'.$Trade_Init_list[$Trade_Init_level]['Port'].',"Server" : 1}}';
                                         $con->send($msg);
                                    }else{
                                       //重新发送普通行情初始化请求
                                        $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                                        $con->send($msg);
                                    }
                                }
                                
                            }else{
                                $Trade_Init_list = Cache::get('Trade_Init_list');
                                $Trade_Init_level = Cache::get('Trade_Init_level')+1;
                                $Trade_Init_conf = Cache::get('Trade_Init_conf');
                                //如果获取接点列表错误，就断开服务
                                if(is_array($Trade_Init_list) && $Trade_Init_level < count($Trade_Init_list)){ //未进行数组下标最大值效验bug
                                    Cache::inc('Trade_Init_level');//行情接点自增
                                    echo $Trade_Init_list[$Trade_Init_level]['IP'];
                                     $msg = '{"req":"Trade_Login","rid":"2","para":{"IP" : "'.$Trade_Init_list[$Trade_Init_level]['IP'].'","Port" :'.$Trade_Init_list[$Trade_Init_level]['Port'].',"Server" : 1}}';
                                     $con->send($msg);
                                }else{
                                   //重新发送普通行情初始化请求
                                    $msg = '{"req":"Trade_Init","rid":"1","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 1,"TryConn" : 0,"GetCount" : 1,"Core":'.$login_arr['core'].'}}';
                                    $con->send($msg);
                                }
                            }
                        }
                        break;
                    case '2001': //心跳检测交易端断开后交易端登录
                        if(isset($datas['data']['Status'])){
                          if($datas['data']['Status'] > 0){
                             //重新登录交易通系统请求 rid 1001开始
                              $server_msg = '{"req":"Server_Login","rid":"1001","para":{"LoginID" : "'.$login_arr['user'].'","LoginPW" : "'.$login_arr['password'].'","Encode" : 0}}';
                              $con->send($server_msg);
                          }
                        }
                        break;
                    case '1001': //交易端登录
                        if(isset($datas['utype'])){
                            Cache::set('Server_Login_data',$datas['data']); 
                            //Broker 为券商ID 根据实际券商更改
                            $jsmsg = '{"req":"Trade_Init","rid":"1002","para":{"Broker" : '.$login_arr['broker'].',"Net" : 0,"Server" : 2,"ClientVer" : "","TryConn" : 3,"Core" : '.$login_arr['core'].'}}';
                            $con->send($jsmsg);
                        }
                        break;
                    case '1002'://交易初始化获取接点列表 rid =1002
                        if(isset($datas['time'])){
                            if(is_array($datas['data'])){
                                 Cache::set('Server_Init_list',$datas['data']);
                                 Cache::set('Server_Init_level',0);
                                 //安全登录 ID 为系统后台获得，ReportSuccess委托实际成交轮询定时器，毫秒单位 ip port 为交易商ip 和 端口
                                  $Server_Login_data = Cache::get('Server_Login_data');
                                //  print_r($Server_Login_data);
                                 $jsmsg = '{"req":"Trade_SafeLogin","rid":"1003","para":{"ID" :'.$Server_Login_data[0]['ID'].',"ReportSuccess" : 1000,"TryConn" : 0,"IP" : "'.$Server_Login_data[0]['IP'].'","Port" : '.$Server_Login_data[0]['Port'].'}}';
                                 $con->send($jsmsg);

                             }
                        }
                        break;
                    case '1003': //获取交易服务器登录结果
                         if(isset($datas['event']) && isset($datas['data']['ID']) ){
                            if($datas['data']['ID'] >0 && $datas['event'] == 'Trade_LoginEvent'){
                                Cache::set('Server_Login_status',1); //登录成功
                                echo "Server_Login_success";
                            }
                         }
                         if(isset($datas['data']['ErrInfo'])){  //未进行数组下标最大值效验bug
                            $Server_Init_list = Cache::get('Server_Init_list');
                            $Server_Init_level = Cache::get('Server_Init_level');
                            $Server_Login_data = Cache::get('Server_Login_data');
                            if(is_array($Server_Init_list) && $Server_Init_level < count($Server_Init_list)){
                                error_log(print_r($Server_Init_list,1),3,'Server_Init_list.txt');
                                echo $Server_Init_list[$Server_Init_level]['IP']; echo "---";
                                 Cache::inc('Server_Init_level');//交易接点自增
                                 $jsmsg = '{"req":"Trade_SafeLogin","rid":"1003","para":{"ID" :'.$Server_Login_data[0]['ID'].',"ReportSuccess" : 1000,"TryConn" : 0,"IP" : "'.$Server_Init_list[$Server_Init_level]['IP'].'","Port" : '.$Server_Init_list[$Server_Init_level]['Port'].'}}';
                                 $con->send($jsmsg);
                            }else{
                                echo "交易服务失败";
                            }
                         }
                        break;

                    case '1007': //买入委托
                        // 执行SQL
                         $insert_id = $db->insert('tsp_notify')->cols(array(
                                                                  'type'=>'买入委托',
                                                                  'info'=>$data,
                                                                  'add_time'=>time()))->query();
                        error_log(print_r($data,1),3,'2buss.txt');
                        if(isset($datas['event'])){
                            if($datas['event'] == 'Trade_SendOrderEvent'){
                                 if(isset($textcontent[$datas['rid']])){
                                        $textcontent[$datas['rid']]->send($data);
                                        unset($textcontent[$datas['rid']]);
                                    }
                            }
                        }
                        break;
                    case '1008': //卖出委托
                      
                        // 执行SQL
                         $insert_id = $db->insert('tsp_notify')->cols(array(
                                                                  'type'=>'卖出委托',
                                                                  'info'=>$data,
                                                                  'add_time'=>time()))->query();
                        if(isset($datas['event'])){
                            if($datas['event'] == 'Trade_SendOrderEvent'){
                                 if(isset($textcontent[$datas['rid']])){
                                        $textcontent[$datas['rid']]->send($data);
                                        unset($textcontent[$datas['rid']]);
                                    }
                            }
                        }
                        break;
                    
                    default:
                     //error_log(print_r($data,1),3,'rizhi.log');
                      if(isset($textcontent[$datas['rid']])){
                        //error_log(print_r($datas['rid'],1),3,'rizhi555.log');
                          $textcontent[$datas['rid']]->send($data);
                          unset($textcontent[$datas['rid']]);
                      }
                        break;
                }

          /*   echo 2;
            //error_log()
             foreach ($inner_text_worker->connections as $connect) {
                   // echo $connect;
                    $connect->send($data);
                }  */                
        
        };
          
        $con->connect();
        $worker->uidConnections = $con;
      
       

       
        
   
    }
}
