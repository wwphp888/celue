<?php

namespace app\index\job;
use app\index\model\Invest as InvestModel;
use app\index\model\Match as MatchModel;
use think\queue\Job;
use think\Db;
class Hello{






	 /**
       * fire方法是消息队列默认调用的方法
       * @param Job            $job      当前的任务对象
       * @param array|mixed    $data     发布任务时自定义的数据
       */
     /* public function fire(Job $job,$data){
      	
      	 $isJobDone = $this->done($data);       
        	  
          //print("<info>Hello Job is Fired at " . date('Y-m-d H:i:s') ."</info> \n");

        
          if ($isJobDone) {
              //如果任务执行成功， 记得删除任务
              $job->delete();
              //print("<info>Hello Job has been done and deleted"."</info>\n");
          }else{
              if ($job->attempts() > 3) {
                  //通过这个方法可以检查这个任务已经重试了几次了
                  //print("<warn>Hello Job has been retried more than 3 times!"."</warn>\n");
  				$job->delete();
                  // 也可以重新发布这个任务
                  //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                  //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
              }
          }
      }*/
      public function updateprice(Job $job,$data){


      	$isJobDone = $this->done($data);       
        	  
        
          if ($isJobDone) {
   
              $job->delete();
 
          }else{
              if ($job->attempts() > 3) {
  
  				$job->delete();

              }
          }


      }

      /*更新当前价格并处理
      */
      public function updateorder(Job $job,$data){


      	$isJobDone = $this->doneorder($data);       
        	  
        
          if ($isJobDone) {
   
              $job->delete();
 
          }else{
              if ($job->attempts() > 3) {
  
  				      $job->delete();

              }
          }


      }
      public function done($data){
      		$info = json_decode($data,true);
      		$price = json_decode(get_socket_info('{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$info['code'].'","JsonType" : 1,"Server" : 1}}'),true);	

      		$result=Db::name('gupiao_list')->where('id',$info['id'])->setField('time_price',$price['data'][1][4]);	
      		if($result!== false){

      			 return true;
      		
      		}else{


      			 return false;
      		}


      }
      public function doneorder($data){
          //判断是否为开盘中
         //return true;
          //股票开市时间验证
        $nowweek = date("w");
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
		$lock = false;
        if($nowtime < $time930 || $nowtime > $time1500){
			$lock = true;
          // return true;
        }
        if($nowtime > $time1130 && $nowtime < $time1300){
          $lock = true;
          // return true;
        }
      		$info = json_decode($data,true);
          //获取5档行情
      		//$price = json_decode(get_socket_info('{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$info['gupiao_code'].'","JsonType" : 1,"Server" : 1}}'),true);
         	$price = get_code_info($info['gupiao_code']);
        //error_log(date('Y-m-d H:i:s').PHP_EOL.print_r($price,1),3,'listorde222r.log');
      		$map = [];
          $map['now_price'] = $price[1][3];
          $map['now_price_update'] = time();
          $map['yest_price'] = $price[1][4];
        //error_log(date('Y-m-d H:i:s').PHP_EOL.print_r($price,1),3,'listorder.log');
          if($map['now_price']==0){
          	 return true;
          }
        if(!$lock){
        	
        	error_log('lock',3,'lock.log');
        }else{
        	error_log('unlock',3,'lock.log');
        }
          if($info['type'] == 'match'){
            $result=Db::name('match_order')->where('id',$info['id'])->update($map); 
            if(($price[1][3] <= $info['stop_down'] || $price[1][3] >= $info['stop_win'])&&!$lock){
             
                 $res = MatchModel::SellOneOrder($info['id'],1);
            }

          }elseif($info['type'] == 'trade'){
            $result=Db::name('trade_order')->where('id',$info['id'])->update($map); 
           // error_log(date('Y-m-d H:i:s').PHP_EOL.print_r($result,1),3,'listorder111.log');
            if(($price[1][3] <= $info['stop_down'] || $price[1][3] >= $info['stop_win'])&&!$lock){
                 $res = InvestModel::sellorder($info['id']);
            }
          }
        	$lock = false;
      		if($result!== false){
      			 return true;
      		
      		}else{
      			 return false;
      		}





      }
      

}
























