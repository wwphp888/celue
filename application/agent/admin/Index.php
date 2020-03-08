<?php

namespace app\agent\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\agent\model\Agent as AgentModel;
use app\vip\model\Vip as VipModel;
use think\Db;
use think\Hook; 

class Index extends Admin{

	public function index(){

		$map = $this->getMap();
        $order = $this->getOrder();
        $dlevel = array(1=>'一级代理',2=>'二级代理',3=>'三级代理');
       // var_dump($dlevel[1]);
        $data_list = AgentModel::where($map)->order($order)->paginate()->each(function($item, $key) use ($dlevel){
		    $item->parent_name = AgentModel::where('id',$item->agent_parent)->value('agent_username');
            //$item->count_num = VipModel::where('id',$item->agent_parent)->value('agent_username');
         	 $item->has_vip = '查看';
             $item->agent_level = $dlevel[$item->agent_level];
		});
        $reback = [
                'title' => '返回上一页',
                'icon'  => 'fa fa-fw fa-reply',
                'href'  => 'javascript:history.back(-1)'
            ];

         return ZBuilder::make('table')
         ->setTableName('agent') // 设置数据表名
         ->setPageTitle('代理商列表')
         ->setSearchArea([['text', 'agent_username', '代理商名称'],])
         ->addColumns([ // 批量添加列
	        ['id', 'ID'],
	        ['agent_code', '机构码','link',url('agent/index/index',['search_field'=>'agent_parent','keyword'=>'__id__'])],
	        ['agent_username', '名称'],
            ['agent_level','级别'],
            ['parent_name','所属上级'],
	        ['agent_money','余额'],
	        ['agent_phone','手机号'],
	        ['agent_realname','真实姓名'],
	        ['agent_idcard','身份证号码'],
	         ['agent_banknumber', '银行卡号'],
	        
            ['agent_initprice','成本价'],
            ['agent_rate', '返佣比例'],
           	['has_vip', '查看所属会员','link',url('vip/index/index',['search_field'=>'agent_id','keyword'=>'__id__'])],
           
	        ['agent_time', '创建时间','datetime'],
	        ['right_button', '操作', 'btn']
	    ])
         ->setRowList($data_list) // 设置表格数据

         ->addTopButtons(['add', 'delete']) // 添加编辑和删除按钮
         ->addRightButtons(['edit', 'delete']) // 添加编辑和删除按钮
         ->setColumnWidth('id', 30)
         ->addTopSelect('agent_level', '级别', $dlevel)
         ->addTopButton("custom",$reback)
         ->fetch();

	}


	public function add(){
		 if ($this->request->isPost()) {

		 	$data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Agent');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            $parent =AgentModel::get($data['agent_parent']);

            if($parent){
            if($parent->agent_level < 3){

            		$data['agent_level'] = ++$parent->agent_level;
            }else{

            		$this->error('级别最多至三级');
            }
	        }else{

	        	$data['agent_level'] = 1;

	        }
	         //print_r($data);exit;
            if ($user = AgentModel::create($data)) {
                //Hook::listen('vip_add', $user);
                // 记录行为
                //action_log('vip_add', 'vip', $user['id'], UID);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }



		 }

		 return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题

            ->addFormItems([ // 批量添加表单项
            	['select', 'agent_parent', '所属代理', '所属上级', AgentModel::getMenuTree(0, '', '')],
                ['text', 'agent_username', '用户名', '必填，可以填手机号'],
                ['password', 'agent_password', '密码', '必填，6-20位'],
                ['text', 'agent_code', '机构码', '必填'],
                ['text', 'agent_realname', '真实姓名'],
                ['text', 'agent_idcard', '身份证号'],
                ['text', 'agent_bankname', '银行名称'],
                ['text', 'agent_banknumber', '银行卡号'],
                //['text', 'agent_initprice', '成本价'],
                ['text', 'agent_rate', '返佣比例'],
            ])

            ->fetch();



        }
    	public function edit($id = null){


		 if ($id === null) $this->error('缺少参数');

		 if ($this->request->isPost()) {

		 		 $data = $this->request->post();
		 		             // 验证
	            $result = $this->validate($data, 'Agent.update');
	            // 验证失败 输出错误信息
	            if(true !== $result) $this->error($result);
	            unset($data['agent_parent']);
	            // 如果没有填写密码，则不更新密码
	            if ($data['agent_password'] == '') {
	                unset($data['agent_password']);
	            }
                if (AgentModel::update($data)) {

                if ($data['affect_money']) {
                    agent_money_log($data['affect_money'],$data['id'],4,'管理员调整');
                }            
                $user = AgentModel::get($data['id']);
                //Hook::listen('vip_edit', $user);
                // 记录行为
                //action_log('vip_edit', 'vip', $user['id'], UID, get_nickname($user['id']));
                $this->success('编辑成功', url('index'));
	            } else {
	                $this->error('编辑失败');
	            }

		 }
		 $info = AgentModel::where('id', $id)->find();
		  unset($info['agent_password']);
		 return ZBuilder::make('form')
		    ->setPageTitle('修改')
		    ->addFormItems([
		    	['hidden', 'id'],
                ['select', 'agent_parent', '所属代理', '所属上级', AgentModel::getMenuTree(0, '', '')],
                ['text', 'agent_username', '用户名', '必填，可以填手机号'],
                ['password', 'agent_password', '密码', '必填，6-20位'],
                ['text', 'agent_phone', '手机号'],
                ['text', 'agent_rate', '返佣比例'],
                ['text', 'agent_initprice', '成本价'],
                ['text', 'agent_realname', '真实姓名'],
                ['text', 'agent_idcard', '身份证号'],
                ['text', 'agent_bankname', '银行名称'],
                ['text', 'agent_banknumber', '银行卡号'],
                ['text', 'affect_money', '调整金额(正数为增加，反之为减少)',],
		    ])
		    ->setFormData($info)
		    ->fetch();


	}    
	public function delete($record =[]){


        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');

        $agent_username = AgentModel::where('id', 'in', $ids)->column('agent_username');

        return parent::setStatus('delete', ['page_delete', 'agent', 0, UID, implode('、', $agent_username)]);

	}



}