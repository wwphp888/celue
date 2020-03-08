<?php
namespace app\vip\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\vip\model\Vip as VipModel;
use app\vip\model\Recharge as RechargeModel;
use think\Db;
use think\Hook; 



class Recharge extends Admin{



		public function index(){

		$map = $this->getMap();
        $order = $this->getOrder();
        $map['recharge_type'] = 1;
        $data_list = RechargeModel::with('vip')->where($map)->order($order)->order("id desc")->paginate();
        foreach ($data_list as $key => $value) {
        	$value->vip_name = $value->vip->vip_name;
        }
        // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('online_export',http_build_query($this->request->param()))
        ];
        $btn_access = [
			    'title' => '手动审核',
			    'icon'  => 'fa fa-fw fa-hand-peace-o',
			    'class' => 'btn btn-xs btn-default ajax-get confirm',
			    'href'  => url('audit', ['id' => '__id__']),
			    'data-title' => '真的要手动审核通过吗'
		];
        return ZBuilder::make('table')
         ->setTableName('vip_recharge') // 设置数据表名
         ->setPageTitle('充值列表')
         ->setSearchArea([['text', 'recharge_order', '订单号'],])
         ->addColumns([ // 批量添加列
	        ['id', 'ID'],
	        ['recharge_order', '订单号'],
	        ['recharge_amount', '金额'],
	        ['recharge_status','状态','status','', ['未付款', '成功', '失败','手动审核通过']],
	        ['vip_name','所属会员'],
	        ['recharge_type','充值类型'],
	        ['recharge_time', '创建时间','datetime'],
	        ['right_button', '操作', 'btn']
	    ])
         ->setRowList($data_list) // 设置表格数据
         ->addRightButton('custom',$btn_access) // 添加编辑和删除按钮
         ->addTopButton('custom', $btn_excel) // 添加导出按钮
         ->replaceRightButton(['recharge_status' => ['in', '1,2,3']], '<button class="btn btn-danger btn-xs" type="button" disabled>不可操作</button>')
         ->setColumnWidth('id', 30)
         ->fetch();



		}
	public function online_export(){
       $map = $this->getMap();
        $order = $this->getOrder();
        $map['recharge_type'] = 1;
        $data_list = RechargeModel::with('vip')->where($map)->order($order)->order("id desc")->paginate();
      
        foreach ($data_list as $key => $value) {
        	$data_list[$key]['vip_name'] = $value->vip->vip_name;
            $data_list[$key]['recharge_time'] = date("Y-m-d H:i:s",$value['recharge_time']);
            switch ($value['recharge_status']) {
            	case '0':
            		$msg = '未付款';
            		break;
            	case '1':
            		$msg = '成功';
            		break;
            	case '2':
            		$msg = '失败';
            		break;
            	case '3':
            		$msg = '手动审核通过';
            		break;
            	
            	default:
            		$msg = '未知状态';
            		break;
            }
            $data_list[$key]['recharge_status'] = $msg;
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['vip_name', 'auto', '用户名'],
            ['recharge_order', 'auto', '订单号'],
            ['recharge_amount', 'auto', '金额'],
            ['recharge_status', 'auto', '状态'],
            ['recharge_type', 'auto', '充值类型'],
            ['recharge_time', 'auto', '创建时间']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['线上充值列表', $cellName, $data_list]);
    }
		public function payoff(){

		$map = $this->getMap();
        $order = $this->getOrder();
        $map['recharge_type'] = 2;
      
        if(isset($map['vip_name'])){
            $map['recharge_vip'] = Db::name("vip")->where("vip_name",$map['vip_name'][1])->value('id');
            unset($map['vip_name']);
        }
        $data_list = RechargeModel::with('vip')->where($map)->order($order)->order("id desc")->paginate();
        foreach ($data_list as $key => $value) {
        	$value->vip_name = $value->vip->vip_name;
        }
        // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('offpay_export',http_build_query($this->request->param()))
        ];
        $btn_access = [
			    'title' => '手动审核成功',
			    'icon'  => 'fa fa-fw fa-hand-peace-o',
			    'class' => 'btn btn-xs btn-default ajax-get confirm',
			    'href'  => url('audit', ['id' => '__id__']),
			    'data-title' => '真的要手动审核通过吗'
		];
		$btn_error = [
			    'title' => '手动审核失败',
			    'icon'  => 'fa fa-fw fa-times-circle-o',
			    'class' => 'btn btn-xs btn-default ajax-get confirm',
			    'href'  => url('auditerrpr', ['id' => '__id__']),
			    'data-title' => '真的要手动审核失败吗'
		];
        $bind_title = <<<JS
<script>
$(".builder-table-body tr").each(function(){
    $(this).find('td').eq(6).attr('title',$(this).find('td').eq(6).text());
    $(this).find('td').eq(8).attr('title',$(this).find('td').eq(8).text());
    $(this).find('td').eq(9).attr('title',$(this).find('td').eq(9).text());
})

</script>
JS;
        return ZBuilder::make('table')
         ->setTableName('vip_recharge') // 设置数据表名
         ->setPageTitle('充值列表')
         ->setSearchArea([['text', 'vip_name', '所属会员'],])
         ->addColumns([ // 批量添加列
	        ['id', 'ID'],
	        ['recharge_amount', '金额'],
	        ['recharge_status','状态','status','', ['未审核', '成功', '失败']],
	        ['vip_name','所属会员'],
	        ['recharge_type','充值类型'],
	        ['recharge_title','标题'],
	        ['recharge_bankname','银行名称'],
	        ['recharge_number','银行账号'],
	        ['recharge_info','备注'],
	        ['recharge_time', '创建时间','datetime'],
	        ['right_button', '操作', 'btn']
	    ])
         ->setRowList($data_list) // 设置表格数据
         ->addRightButton('custom',$btn_access) // 添加编辑和删除按钮
         ->addRightButton('custom',$btn_error) // 添加编辑和删除按钮
         ->addTopButton('custom', $btn_excel) // 添加导出按钮
         ->replaceRightButton(['recharge_status' => ['in', '1,2']], '<button class="btn btn-danger btn-xs" type="button" disabled>不可操作</button>')
         ->setColumnWidth('id', 30)
         ->setColumnWidth('recharge_number', 170)
         ->setExtraJs($bind_title)
         ->fetch();



		}
public function offpay_export(){
      $map = $this->getMap();
        $order = $this->getOrder();
        $map['recharge_type'] = 2;
        $data_list = RechargeModel::with('vip')->where($map)->order($order)->order("id desc")->paginate();
      
        foreach ($data_list as $key => $value) {
        	$data_list[$key]['vip_name'] = $value->vip->vip_name;
            $data_list[$key]['recharge_time'] = date("Y-m-d H:i:s",$value['recharge_time']);
            switch ($value['recharge_status']) {
            	case '0':
            		$msg = '未审核';
            		break;
            	case '1':
            		$msg = '成功';
            		break;
            	case '2':
            		$msg = '失败';
            		break;
            	default:
            		$msg = '未知状态';
            		break;
            }
            $data_list[$key]['recharge_status'] = $msg;
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['vip_name', 'auto', '用户名'],
            ['recharge_amount', 'auto', '金额'],
            ['recharge_title', 'auto', '标题'],
            ['recharge_bankname', 'auto', '银行名称'],
            ['recharge_number', 'auto', '银行账号'],
            ['recharge_info', 'auto', '备注'],
            ['recharge_type', 'auto', '充值类型'],
            ['recharge_status', 'auto', '状态'],
            ['recharge_time', 'auto', '创建时间']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['线下充值列表', $cellName, $data_list]);
    }
		public function audit(){

			 if ($this->request->isGet()) {

			 		$data = $this->request->param();
			 		$recharge = RechargeModel::get($data);	

			 		if($recharge->recharge_status!= 0){

			 				$this->error('订单已完成,禁止修改', null, '_close_pop');

			 		}

			 		if($recharge->recharge_type == '线下支付'){
			 			$data['recharge_status'] = 1;
			 		}else{
			 			$data['recharge_status'] = 3;
			 		}

			 		if(RechargeModel::update($data)){

			 		//$recharge->vip->setInc('vip_money',$recharge->recharge_amount);
			 			 money_log($recharge->recharge_amount,$recharge->recharge_vip,1,'充值成功');

			 			$this->success('手动审核完成', null, '_parent_reload');

			 		}else{

			 			$this->error('审核失败', null, '_close_pop');
			 		}

			 }

		}

	public function auditerrpr(){

			 if ($this->request->isGet()) {

			 		$data = $this->request->param();
			 		$recharge = RechargeModel::get($data);	

			 		if($recharge->recharge_status!= 0){

			 				$this->error('订单已完成,禁止修改', null, '_close_pop');

			 		}

			 		if($recharge->recharge_type == '线下支付'){
			 			$data['recharge_status'] = 2;
			 		}

			 		if(RechargeModel::update($data)){

			 		//$recharge->vip->setInc('vip_money',$recharge->recharge_amount);
			 			// money_log($recharge->recharge_amount,$recharge->recharge_vip,1,'充值成功');

			 			$this->success('手动审核完成', null, '_parent_reload');

			 		}else{

			 			$this->error('审核失败', null, '_close_pop');
			 		}

			 }

		}





}