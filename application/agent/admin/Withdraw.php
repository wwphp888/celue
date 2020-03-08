<?php
namespace app\agent\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;

use think\Db;
use think\Hook; 

 

class Withdraw extends Admin{



		public function index(){

		$map = $this->getMap();
        $order = $this->getOrder();
      if(isset($map['agent_name'])){
           // $map['withdraw_agent'] = Db::name("agent")->where("agent_username",$map['agent_name'][1])->value('id');
        	$map['v.agent_username'] = $map['agent_name'];
            unset($map['agent_name']);
        }
		$data_list = Db::name('agent_withdraw')
          			->alias('a')
          			->field('a.*,v.agent_username')
          			->join('__AGENT__ v','a.withdraw_agent = v.id','LEFT')
          			->where($map)
          			->order('a.id desc')
          			->paginate(10);
		//print_r($data_list);exit;
        // 导出按钮 
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
        $btn_success = [
			    'title' => '审核通过',
			    'icon'  => 'fa fa-fw fa-check-circle',
			    'class' => 'btn btn-xs btn-default ajax-get confirm',
			    'href'  => url('audit', ['id' => '__id__',"type"=>'1']),
			    'data-title' => '真的要审核通过吗?(提现成功)',
			    'data-tips' => '该操作为不可逆的,请谨慎操作'
		];
		$btn_error = [
			    'title' => '审核不通过',
			    'icon'  => 'fa fa-fw fa-times-circle',
			    'class' => 'btn btn-xs btn-default ajax-get confirm',
			    'href'  => url('audit', ['id' => '__id__',"type"=>'2']),
			    'data-title' => '真的要审核不通过吗？(提现失败)',
			    'data-tips' => '该操作为不可逆的,请谨慎操作'
		];
        return ZBuilder::make('table')
         ->setTableName('agent_withdraw') // 设置数据表名
         ->setPageTitle('提现列表')
         ->setSearchArea([['text', 'agent_name', '用户名'],])
         ->addColumns([ // 批量添加列
	        ['id', 'ID'],
	       // ['vip_name','所属会员'],
	        //['vip_phone','手机号'],
	        ['agent_username', '代理商'],
	        ['withdraw_amount', '提现金额'],
	        ['withdraw_status','状态','status','', ['未审核', '已成功', '已失败']],
	        
	        ['withdraw_time', '创建时间',],
	        ['withdraw_bank', '银行名称'],
	        ['withdraw_card', '银行卡号'],
	        ['withdraw_realname', '真实姓名'],
	        ['right_button', '操作', 'btn']
	    ])
         ->setRowList($data_list) // 设置表格数据
         //->addRightButton('custom', [], ['area' => ['800px', '90%'], 'title' => '<i class="fa fa-user"></i> 这是新标题'])
         ->addRightButton('custom',$btn_success) // 添加编辑和删除按钮
         ->addRightButton('custom',$btn_error) // 添加编辑和删除按钮
        // ->addTopButton('custom', $btn_excel) // 添加导出按钮
         ->replaceRightButton(['withdraw_status' => ['in', '1,2']], '<button class="btn btn-danger btn-xs" type="button" disabled>不可操作</button>')
         ->setColumnWidth('id', 30)
          ->setColumnWidth('withdraw_time', 150)
         ->setColumnWidth('withdraw_card', 250)
         ->fetch();



		}
public function export(){
     $map = $this->getMap();
        $order = $this->getOrder();
        $data_list = WithdrawModel::with('vip')->where($map)->order($order)->paginate();
        foreach ($data_list as $key => $value) {
        	$data_list[$key]['vip_name'] = $value->vip->vip_name;
        	$data_list[$key]['vip_phone'] = $value->vip->vip_phone;
        	 switch ($value['withdraw_status']) {
            	case '0':
            		$msg = '未审核';
            		break;
            	case '1':
            		$msg = '已成功';
            		break;
            	case '2':
            		$msg = '已失败';
            		break;
            	default:
            		$msg = '未知状态';
            		break;
            }
            $data_list[$key]['withdraw_status'] = $msg;
            $data_list[$key]['withdraw_time'] = date("Y-m-d H:i:s",$value['withdraw_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['vip_name', 'auto', '用户名'],
            ['vip_phone', 'auto', '手机号'],
            ['withdraw_realname', 'auto', '真实姓名'],
            ['withdraw_amount', 'auto', '提现金额'],
            ['withdraw_bank', 'auto', '银行名称'],
            ['withdraw_card', 'auto', '银行卡号'],
            ['withdraw_status', 'auto', '状态'],
            ['withdraw_time', 'auto', '创建时间']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['提现列表', $cellName, $data_list]);
    }
		public function audit(){

			 if ($this->request->isGet()) {

			 		$data = $this->request->param();
			 		$type = $data['type'];
			 		unset($data['type']);
			 		//$infos = WithdrawModel::get($data);	
					$infos = Db::name('agent_withdraw')->where($data)->find();	
			 		if($infos['withdraw_status']!= 0){

			 				$this->error('已审核,禁止修改', null, '_close_pop');

			 		}
			 		
			 		if($type == 1){
			 			$data['withdraw_status'] = 1;
			 			$res = Db::name('agent_withdraw')->update($data);
			 			agent_money_log($infos['withdraw_amount'],$infos['withdraw_agent'],3,'提现成功');
			 		}else{
			 			$data['withdraw_status'] = 2;
			 			$res = Db::name('agent_withdraw')->update($data);
			 			agent_money_log($infos['withdraw_amount'],$infos['withdraw_agent'],15,'提现失败');
			 		}
			 		

			 		if($res){

			 		$this->success('手动审核完成', null, '_parent_reload');

			 		}else{

			 			$this->error('审核失败', null, '_close_pop');
			 		}

			 }






		}







}