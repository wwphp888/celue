<?php
namespace app\command\home;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use app\socket\home\Index;
class Check extends Command
{
    protected function configure(){
        $this->setName('Check')->setDescription("计划任务 Check");
    }

    protected function execute(Input $input, Output $output){
        $output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/

        $this->test();

        /*** 这里写计划任务列表集 END ***/
        $output->writeln('Date Crontab job end...');
    }

    private function test(){
	       $trade_server = get_code_info('000001');

	       $server_server = get_server_info(); 

         if(!$trade_server[0]||!$server_server[1]){
            echo '未运行';
            exec('/www/wwwroot/celue0121/checksocket.sh');

         }else{

            echo '已在运行';
         }
        /* $bat = "C:\phpstudy\PHPTutorial\celue20190108\celue\check_socket.bat";
         if(!$trade_server[0]||!$server_server[1]){

            exec($bat);

         }*/
       /*$order_list = Db::name('trade_order')->where('status',2)->select();
       
   
       Db::transaction(function()use($order_list){
    

       foreach ($order_list as $key => $value) {
           //print_r($value);exit;  
           $info = json_decode(sendsockt('{"req":"Trade_QueryQuote","rid":"3","para":{"Codes" : "'.$value['gupiao_code'].'","JsonType" : 1,"Server" : 1}}'),true);  
            if($info['data'][1][3]>=$value['stop_win']||$info['data'][1][3]<=$value['stop_down']){

                    $value['status'] = 1;
                    Db::name('trade_order')->update($value);
                  

            }

         }
        });*/

    }
    public function bow(){


       $order_list = Db::name('trade_order')->where('status',2)->select();
       
   
       Db::transaction(function()use($order_list){
    

       foreach ($order_list as $key => $value) {
           //print_r($value);exit;  
           $info = json_decode(sendsockt('{"req":"Trade_QueryQuote","rid":"3","para":{"Codes" : "'.$value['gupiao_code'].'","JsonType" : 1,"Server" : 1}}'),true);  
            if($info['data'][1][3]>=$value['stop_win']||$info['data'][1][3]<=$value['stop_down']){

                    $value['status'] = 1;
                    Db::name('trade_order')->update($value);
                  

            }

         }
        });
       
    }

   public function get_code_info($code){
        
            $info =json_decode(get_socket_info('{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$code.'","JsonType" : 1,"Server" : 1}}'),true)['data'];
             return $info;

    }

}