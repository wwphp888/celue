<?php


namespace app\vip\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\vip\model\Vip as VipModel;
use think\Db;
use think\Hook; 


class Index extends Admin{


	public function index(){

		$map = $this->getMap();
        $order = $this->getOrder();
		if($map['agent_id']){
        
       
         $child =[];
          $this->getchild($child,$map['agent_id']);
       
        $recommendCode = array_column($child,'agent_code');
		 $recommendCode[]= Db::name('agent')->where('id',$map['agent_id'])->value('agent_code');
         
		$map['recommendCode'] = array('in',$recommendCode);	
          unset($map['agent_id']);
        }
      	 
        $data_list = VipModel::where($map)->order($order)->order("id desc")->paginate()->each(function($item, $key){
               // var_dump($item->id);
                //当前持仓市值
                $item->has_ccmoney = Db::name("trade_order")->where("user_id",$item->id)->where("status",2)->SUM("trush_number*now_price");
                //历史盈亏
                $item->old_yingkui = Db::name("trade_order")->where("user_id",$item->id)->where("status",4)->SUM("repay_profits");
                //浮动盈亏
               $fl_list = Db::name("trade_order")->where("user_id",$item->id)->where("status",2)->select();
               $flnum = 0;
               foreach ($fl_list as $ke => $va) {
                   $flnum += round(($va['now_price']-$va['trush_price'])*$va['trush_number'],2);
               }
                $item->float_yingkui = $flnum;
               // var_dump($item->has_ccmoney);
        });
        // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
        $change_money = [
            'title' => '调整金额',
            'icon'  => 'fa fa-fw fa-medkit',
            'href'  => url('change_money', ['id' => '__id__']),
        ];
         return ZBuilder::make('table')
         ->setTableName('vip') // 设置数据表名
         ->setPageTitle('会员列表')
         ->setSearchArea([['text', 'vip_phone', '手机号'],])
         ->addColumns([ // 批量添加列
	        ['id', 'ID'],
	        ['vip_name', '用户名'],
	        ['vip_phone', '手机号'],
          ['buy_type', '实盘状态', 'status', '', ['实盘:success', '模拟:danger']],
          ['recommendCode', '所属代理'],
	        ['vip_money','余额'],
          ['has_ccmoney', '持仓市值'],
          ['old_yingkui', '历史盈亏'],
          ['float_yingkui', '浮动盈亏'],
	        ['vip_realname','真实姓名'],
	        ['vip_idcard','身份证号码'],
	        ['register_time', '创建时间','datetime'],
	        ['last_login_time', '上一次登录时间', 'datetime']	,
	        ['status', '状态', 'switch'],
	        ['right_button', '操作', 'btn']
	    ])
         ->setRowList($data_list) // 设置表格数据
         ->addTopButtons(['add']) // 添加编辑和删除按钮
         ->addRightButtons(['edit']) // 添加编辑和删除按钮
          ->addRightButton('change_money',$change_money,['area' => ['570px', '350px'], 'title' => '调整金额']) // 批量添加右侧按钮
         ->addTopButton('custom', $btn_excel) // 添加导出按钮
         ->setColumnWidth('id', 50)
         ->setColumnWidth('vip_money,has_ccmoney,old_yingkui,float_yingkui', 140)
         ->setColumnWidth('vip_idcard', 200)
         ->setColumnWidth('vip_name,vip_phone,register_time,last_login_time', 145)
         ->fetch();
	}

    public function change_money(){
        $id = request()->param('id');
        $vip_info = Db::name("vip")->where("id",$id)->find();
        if($this->request->isPost()){
            $data = $this->request->Post();
            
            $res = money_log($data['change_money'],$data['id'],4,"管理员调整");
            if($res){
                $this->success("修改成功");
            }else{
                $this->error("修改失败");
            }
        }
       
         return ZBuilder::make('form')
                    ->addHidden('id')
                    ->addText('vip_money', '当前账户余额','','0','','disabled')
                    ->addText('change_money', '调整金额','如需增加则填写正数，减少填写负数')
                    ->setFormData($vip_info)
                    ->fetch();
        
    }
  
    public function getchild(&$child,$pid){
  	
		$list = Db::name('agent')->where('agent_parent',$pid)->select();
    	for($i=0;$i< count($list);$i++){
        
        
        	$child[]=['id'=>$list[$i]['id'],'agent_code'=>$list[$i]['agent_code']];
          	$this->getchild($child,$list[$i]['id']);

        
        }

  
  }
  
     public function export(){
        // 查询数据
        $map = $this->getMap();
        $order = $this->getOrder();

        $data_list = VipModel::where($map)->order($order)->order("id desc")->paginate();
        foreach ($data_list as $key => $value) {
            $data_list[$key]['register_time'] = date("Y-m-d H:i:s",$value['register_time']);
            $data_list[$key]['last_login_time'] = date("Y-m-d H:i:s",$value['last_login_time']);
            $data_list[$key]['status'] = $value['status']=='1'?'正常':'禁用';
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['vip_name', 'auto', '用户名'],
            ['vip_phone', 'auto', '手机号'],
            ['vip_money', 'auto', '余额'],
            ['vip_realname', 'auto', '真实姓名'],
            ['vip_idcard', 'auto', '身份证号码'],
            ['register_time', 'auto', '创建时间'],
            ['last_login_time', 'auto', '上一次登录时间'],
            ['status', 'auto', '状态']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['会员列表', $cellName, $data_list]);
    }
	public function add(){
		 if ($this->request->isPost()) {

		 	$data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Vip');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if ($user = VipModel::create($data)) {
                Hook::listen('vip_add', $user);
                // 记录行为
                action_log('vip_add', 'vip', $user['id'], UID);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }



		 }
		return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['text', 'vip_name', '用户名', '必填，可以填手机号'],
                ['password', 'vip_password', '密码', '必填，6-20位'],
                ['password', 'vip_paypassword', '支付密码', '必填，6-20位'],
                ['text', 'vip_phone', '手机号'],
                ['text', 'vip_idcard', '身份证号'],
                ['text', 'vip_realname', '真实姓名'],
                ['text', 'vip_money', '余额'],
                ['text', 'recommendCode', '推荐码','必填'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->fetch();





	}

	public function edit($id = null){


		 if ($id === null) $this->error('缺少参数');

		 if ($this->request->isPost()) {

		 		 $data = $this->request->post();
		 		             // 验证
	            $result = $this->validate($data, 'Vip.update');
	            // 验证失败 输出错误信息
	            if(true !== $result) $this->error($result);

	            // 如果没有填写密码，则不更新密码
	            if ($data['vip_password'] == '') {
	                unset($data['vip_password']);
	            }
	            if ($data['vip_paypassword'] == '') {
	                unset($data['vip_paypassword']);
	            }
                if (VipModel::update($data)) {
                $user = VipModel::get($data['id']);
                Hook::listen('vip_edit', $user);
                // 记录行为
                action_log('vip_edit', 'vip', $user['id'], UID, get_nickname($user['id']));
                $this->success('编辑成功', cookie('__forward__'));
	            } else {
	                $this->error('编辑失败');
	            }

		 }
		 $info = VipModel::where('id', $id)->find();

		  unset($info['vip_password'], $info['vip_paypassword']);
		 return ZBuilder::make('form')
		    ->setPageTitle('修改')
		    ->addFormItems([
		    	['hidden', 'id'],
                ['text', 'vip_name', '用户名', ''],
                ['password', 'vip_password', '密码','不修改请留空。修改时必填'],
                ['password', 'vip_paypassword', '支付密码', '不修改请留空。修改时必填'],
                ['text', 'vip_phone', '手机号',],
                ['text', 'vip_idcard', '身份证号',],
                ['text', 'vip_realname', '真实姓名',],
                ['text', 'recommendCode', '推荐码',],
                ['radio', 'status', '状态', '', ['禁用', '启用']],
                ['radio', 'buy_type', '实盘状态', '', ['实盘', '模拟']],
		    ])
		    ->setFormData($info)
		    ->fetch();


	}
	public function delete($record =[]){


        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');

        $vip_name = VipModel::where('id', 'in', $ids)->column('vip_name');

        return parent::setStatus('delete', ['page_delete', 'vip', 0, UID, implode('、', $vip_name)]);

	}




}