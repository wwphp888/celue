<?php 

namespace app\expand\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\expand\model\Trade as ExpandModel;
use think\Db;
use think\Hook; 
use think\Cache;
use think\Request;

/**
 * 银行列表控制器
 * @package app\expand\idnex
 */
class Index extends admin{
	public function index(){
		return $this->fetch();
	}
	public function banklist(){
		// 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = Db::name("banklist")->where($map)->order($order)->select();
       // var_dump($data_list);die;
     
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('banklist')
        	->hideCheckbox()
            ->setSearch(['bankname' => '银行名称'],'','',true) // 设置搜索框
            ->addColumns([ // 批量添加数据列
            	['id', 'ID'],
                ['bankname', '银行名称'],
                ['add_time', '添加时间','datetime'],
                ['status', '状态', 'status', '', ['禁用:danger', '启用:success']],
                ['right_button', '操作', 'btn']
            ])

            ->addTopButton('add') // 添加顶部按钮
             ->addRightButtons('enable,disable')
            ->addRightButton('delete')
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}

	public function add(){
		
		if(request()->isPost()){
			$params = request()->param();
			$params['add_time'] = time();
			$res = Db::name("banklist")->insert($params);
			if($res){
				$this->success("添加成功");
			}else{
				$this->error("添加失败");
			}

		}else{
			return ZBuilder::make('form')
			    ->setFormItems(
			      [
			          [
						'type' => 'text',
			            'name' => 'bankname',
			            'title' => '银行名称'
			          ],

			      ]
			    )
			    ->addRadio('status', '是否启用', '', ['1' => '是', '0' => '否'])
			    ->fetch();
		}
		
	}
}