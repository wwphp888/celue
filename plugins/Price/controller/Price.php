<?php

namespace plugins\Price\controller;

use app\common\controller\Common;
use think\Exception;

use think\Queue;

use think\Db;


class Price extends Common{

	/*添加队列任务 
	*/
	public function updateorderlist(){
      
      	/* $nowweek = date("w");
        if($nowweek == '6' || $nowweek=='0'){
          return true;
        }
        $start_time = strtotime(date("Y-m-d"));
        //上午开盘时间9:30  
        $time930 = $start_time + 3600*9+1800;

        //上午休市时间 11:30
        $time1130 = $start_time+3600*11+1800;

        //下午开盘时间 13:00
        $time1300 = $start_time+3600*13;
        //下午休市时间 15:00
        $time1500 = $start_time+3600*15;

        $nowtime = time();

        if($nowtime < $time930 || $nowtime > $time1500){

           return true;
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          
           return true;
        }*/
      
		 $jobHandlerClassName  = 'app\index\job\Hello@updateorder'; 

	      $jobQueueName  	  = "helloJobQueue"; 

	      $match_order = Db::name("match_order")->field('id,gupiao_code,stop_win,stop_down')->where("status",1)->select();
	      if(is_array($match_order)){
	      	foreach ($match_order as $key => $value) {
	      	  $value['type'] = "match";
	      	  $jobData  = json_encode($value);
		      $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );	
		      // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
		      if( $isPushed !== false ){  
		          	echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
			      }else{
			          echo 'Oops, something went wrong.';
			      }
	      	}
	      }
	      
	      	//$tarde_map['status'] = array("in","1,2");
	      $trade_order = Db::name("trade_order")->field('id,gupiao_code,stop_win,stop_down')->where("status",2)->select();
	      if(is_array($trade_order)){
	      	foreach ($trade_order as $key => $value) {
	      	  $value['type'] = "trade";
	      	  $jobData  = json_encode($value);
		      $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );	
		      // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
		      if( $isPushed !== false ){  
		          	echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
			      }else{
			          echo 'Oops, something went wrong.';
			      }
	      	}
	      }
	      

	     /* $gupiao_list=Db::name('gupiao_list')->field('id,code')->where('status',1)->select();  
	        
	      foreach ($gupiao_list as $key => $value) {
	      	
	      	$jobData  = json_encode($value);
		      $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );	
		      // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
		      if( $isPushed !== false ){  
		          	echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
			      }else{
			          echo 'Oops, something went wrong.';
			      }
	      	}*/

	}


	public function updateprice(){





     $jobHandlerClassName  = 'app\index\job\Hello@updateprice'; 

      $jobQueueName  	  = "helloJobQueue"; 

      $gupiao_list=Db::name('gupiao_list')->field('id,code')->where('status',1)->select();  
        
      foreach ($gupiao_list as $key => $value) {
      	
      	$jobData  = json_encode($value);
	      $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );	
	      // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
	      if( $isPushed !== false ){  
	          	echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
		      }else{
		          echo 'Oops, something went wrong.';
		      }
      	}





	}

	public function updateorder(){



		$jobHandlerClassName  = 'app\index\job\Hello@updateorder'; 

     	 $jobQueueName  	  = "helloJobQueue"; 

      	$match_order=Db::name('match_order')->field('id,gupiao_code,stop_down,stop_win')->where('user_id','<>','')->select();  
         
      	foreach ($match_order as $key => $value) {
      	
      	$jobData  = json_encode($value);
	      $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );	
	      // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
	      if( $isPushed !== false ){  
	          	echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
		      }else{
		          echo 'Oops, something went wrong.';
		      }
      	}



	}












}

