<?php
namespace app\command\home;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
class Test extends Command
{
    protected function configure(){
        $this->setName('Test')->setDescription("计划任务 Test");
    }

    protected function execute(Input $input, Output $output){
        $output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/

        $this->test();

        /*** 这里写计划任务列表集 END ***/
        $output->writeln('Date Crontab job end...');
    }

    private function test(){
    
	
	
		plugin_action('Price', 'Price', 'updateorderlist');
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
}