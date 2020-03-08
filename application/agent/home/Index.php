<?php
namespace app\agent\home;

use app\agent\home\Common;
use think\Db;
use think\Request;
use util\Tree;
use think\helper\Hash;
session_start();
class Index extends Common{

	public function _initialize(){

		parent::_initialize();
		if(!$this->agent_id){
			$this->redirect('/agent/common/login');
		}
		$agent_info =Db::name('agent')->where('id',$this->agent_id)->find();
		$this->assign('agent_info',$agent_info);

	}

	public function index (){
		

		return $this->fetch();

	}
  public function code_view(){
  
  	 $code =Db::name('agent')->where('id',$this->agent_id)->value('agent_code');
  
  	 $url='http://'.$_SERVER["SERVER_NAME"]."/wap/#/login?code=".$code;	
  	
  	plugin_action('Qrcode/Qrcode/generate', [$url, APP_PATH.'test.png']);
  
  
  }

	public function main(){

		$html = [];
		$this->getChild($html,$this->agent_id);
		$code_list = array_column($html,'Code');
		array_push($code_list, Db::name('agent')->where('id',$this->agent_id)->value('agent_code'));
		$user=Db::name('vip')->field('id')->where('recommendCode','in',$code_list)->select();	
		$userid = array_column($user,'id');

		$data['reg']=count($user);
		$data['trade_money']=Db::name('trade_order')->where('user_id','in',$userid)->sum('credit_money');
		$data['tody_trade_money']=Db::name('trade_order')->where('user_id','in',$userid)->whereTime('deal_time','d')->sum('credit_money');
		$data['recharge_money']=Db::name('vip_recharge')->where('recharge_vip','in',$userid)->where('recharge_status','eq',1)->sum('recharge_amount');
		$data['tody_reg'] = Db::name('vip')->whereTime('register_time','d')->count();
      	$img ='http://'.$_SERVER["SERVER_NAME"]."/agent/index/code_view";	

		$this->assign('img',$img);
		$this->assign('data',$data);
		return $this->fetch();

	}

	public function loginout(){

		unset($_SESSION['agent_id']);



		$this->success('退出成功');



	}
	public function member(){

		$param=Request::instance()->param();

		
		if($param){
		$html = [];
		$this->getChild($html,$this->agent_id);

		$agent_allid = array_column($html,'id');

		$search_id =Db::name('agent')->where('agent_username',$param['username'])->value('id');

		if(in_array($search_id, $agent_allid)){

			$member_list = $this->tree2($this->agent_id,0,$search_id);	
		}

		}else{

			$member_list = $this->tree2($this->agent_id,0);
		}

	
		$this->assign('member_list',$member_list);




		return $this->fetch();

	}
	public function vip(){


		$param=Request::instance()->param();
		if($param){
			$map['vip_name'] = $param['username'];
		}
		$html = [];
		$this->getChild($html,$this->agent_id);
		$code_list = array_column($html,'Code');
		$code_list[] = Db::name('agent')->where('id',$this->agent_id)->value('agent_code');
		$map['recommendCode'] = array('in',$code_list);
		$list = Db::name('vip')->where($map)->paginate(10);
		
		$this->assign('list',$list);
		return $this->fetch();

	}
	public function trade(){

		$child =Tree::getChildsId(Db::name('agent')->where($where)->column('id,agent_parent as pid,agent_username as title'),$this->agent_id);
		$child[] = $this->agent_id;

		$map['v.recommendCode']=array('in',Db::name('agent')->where('id','in',$child)->column('id,agent_code'));
		if(request()->param('contrller')){


			$map['t.status'] = input('contrller');

		}
		if(request()->param('username')){


			$map['v.vip_name'] = input('username');

		}
		//print_r($map);exit;
		$list = Db::name('trade_order')
				->alias('t')
				->field('t.*,v.vip_name')
				->join('__VIP__ v','t.user_id = v.id','LEFT')
				->where($map)
				->paginate(10);
		$this->assign('map',$map);		
		$this->assign('list',$list);
		return $this->fetch();
	}
	public function get_attr($a,$pid){
	    $tree = array();                                //每次都声明一个新数组用来放子元素
	    foreach($a as $v){
	        if($v['pid'] == $pid){                      //匹配子记录
	            $v['children'] = $this->get_attr($a,$v['id']); //递归获取子记录
	            if($v['children'] == null){
	                unset($v['children']);             //如果子元素为空则unset()进行删除，说明已经到该分支的最后一个元素了（可选）
	            }
	            $tree[] = $v;                           //将记录存入新数组
	        }
	    }
	    return $tree;                                  //返回新数组
	}

	public function getChild(&$html,$parent_id){
	  $childlist = Db::name('agent')->where('agent_parent',$parent_id)->select();
	  for($i = 0;$i<count($childlist);$i++){
		  $html[] = array('id'=>$childlist[$i]['id'],'agent_username'=>$childlist[$i]['agent_username'],'agent_parent'=>$childlist[$i]['agent_parent'],'Code'=>$childlist[$i]['agent_code']);
		  $this->getChild($html,$childlist[$i]['id']);
	  }
	  
    } 
    public function tree2($pid=0,$level=0,$searchid=0){
        
       
     	if($searchid){
     		 $data = Db::name('agent')->where('id',$searchid)->select();
     	}else{
     		 $data = Db::name('agent')->where('agent_parent',$pid)->select();	
     	}
       

        $level ++;
        if(!empty($data)){
            $tree = array();
            foreach ($data as $val) {


                $child = $this ->tree2($val['id'],$level);

                
                switch ($val['agent_level']) {
                	case '1':
                		$level_name = '一级代理';
                		break;
                	case '2':
                		$level_name = '二级代理';
                		break;
                	case '3':
                		$level_name = '三级代理';
                		break;
                	default:
                		break;
                }
                //$tree[] = array('self'=>$val,'child'=>$child,'level'=>$level,'level_name'=>$level_name);
                $val['child'] = $child;
                $val['level'] = $level;
                $val['level_name'] =$level_name;
                $tree[] = $val;
            }
        }

        return $tree;
    }


    public function curl_post($data){
    	// 1. 初始化
		 $ch = curl_init();
		 // 2. 设置选项，包括URL
		 curl_setopt($ch,CURLOPT_URL,"tcp://127.0.0.1");
		 curl_setopt($ch, CURLOPT_PORT, 5678);
		 curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		 curl_setopt($ch,CURLOPT_HEADER,0);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 		 curl_setopt($ch, CURLOPT_POST,true);
 		 $result =curl_exec($ch);
 		 curl_close($ch);
 		 return $result;
    }

    public function agent_info(){

    	if(request()->isPost()){

    		$data['agent_bankname']	 = input('post.agent_bankname');
    		$data['agent_banknumber']	 = input('post.agent_banknumber');
    		$data['agent_idcard']	 = input('post.agent_idcard');
    		$data['agent_realname']	 = input('post.agent_realname');
    		$data['agent_phone']	 = input('post.agent_phone');
    		//$data['agent_rate']	 = input('post.agent_rate');
    		$data['id'] = $this->agent_id;
    		if($data['agent_password']){

    			$data['agent_password'] = Hash::make((string)$data['agent_password']);

    		}
    		$result =Db::name('agent')->update($data);
    		if($result!==false){

    			return json(['status'=>1,'message'=>'更新完成']);
    		}else{

    			return json(['status'=>0,'message'=>'更新失败']);

    		}


    	}else{
    		$info=Db::name('agent')->field(['agent_password','agent_money'],true)->where('id',$this->agent_id)->find();

    		$this->assign('info',$info);


    	    return $this->fetch();
    	}    
    }
  public function agent_withdraw(){
  
  	$agent_info=Db::name('agent')->field('agent_password',true)->where('id',$this->agent_id)->find();
  	if(request()->isPost()){
    
    	  $data['withdraw_amount'] = input('post.withdraw_money');
        
    	if($agent_info['agent_money'] < $data['withdraw_amount']){
            
            	return json(['status'=>0,'message'=>'提现金额超出余额']);
            
            }
      	if($agent_info['agent_bankname']=='' || $agent_info['agent_banknumber']==''){
        
        		return json(['status'=>0,'message'=>'请先绑定下银行卡号']);
        }
    	$data['withdraw_agent'] = $agent_info['id'];
      	$data['withdraw_time'] = date('Y-m-d H:i:s');
      	$data['withdraw_card'] = $agent_info['agent_banknumber'];
      	$data['withdraw_realname'] = $agent_info['agent_realname'];
      	$data['withdraw_bank'] = $agent_info['agent_bankname'];
      	$data['withdraw_status'] = 0;
      	 Db::startTrans();
      	try{
        $res = Db::name('agent_withdraw')->insertGetId($data);
      	$res2 =agent_money_log($data['withdraw_amount'],$data['withdraw_agent'],2,'提现冻结');
         Db::commit();
         return json(['status'=>1,'message'=>'申请成功']);
        }catch (\Exception $e) {


				 Db::rollback();
			 return json(['status'=>0,'message'=>'申请失败']);
			}

    }else{
    
    
    	

    		$this->assign('agent_money',$agent_info['agent_money']);


    	    return $this->fetch();
    
    
    
    
    
    }
  
  	
  
  
  
  
  }

    public function money_log(){
    			$map['v.id'] = $this->agent_id;
    			$list = Db::name('agent_record')
    			->alias('t')
				->field('t.*,v.agent_username')
				->join('__AGENT__ v','t.agent_id = v.id','LEFT')
				->where($map)
                ->order('t.id desc')
				->paginate(10)->each(function($item,$key){

					$item['type'] = config("MEONEY_TYPE")[$item['type']];
					return $item;
				});

				$this->assign('map',$map);		
				$this->assign('list',$list);
				return $this->fetch();


    }

	public function member_add(){
    
    	if(request()->isPost()){
        	$data = input('post.');
        	
        	$level =Db::name('agent')->where('id',$this->agent_id)->value('agent_level');
        
        	if($level > 2){
            
            	return json(['status'=>0,'message'=>'你不能添加代理商']);
            
            }  
           $data['agent_level'] = ++$level;
         // print_r($data['agent_level']);exit;
        	$code =Db::name('agent')->where('agent_code',$data['agent_code'])->find();
        
        	if($code){
            
            	return json(['status'=>0,'message'=>'机构码已被使用']);
            
            } 
          $user =Db::name('agent')->where('agent_username',$data['agent_username'])->find();
        
        	if($user){
            
            	return json(['status'=>0,'message'=>'用户名已被使用']);
            
            } 
        	if(!is_numeric($data['agent_rate'])){
            
            	return json(['status'=>0,'message'=>'返佣比例不合法']);
            }	
          	$data['agent_parent'] = $this->agent_id;
          	$data['agent_time'] = date('Y-m-d H:i:s',time());
          	$data['agent_password'] = Hash::make((string)$data['agent_password']);
        	$res =Db::name('agent')->insert($data);
        
        	return $res? json(['status'=>1,'message'=>'添加成功']): json(['status'=>0,'message'=>'添加失败']);
        
        }else{
        
        
        
        	return $this->fetch();
        
        
        
        
        
        
        }
    
    
    
    
    
    
    
    
    
    
    
    
    }














}