<?php
namespace app\vip\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\vip\model\Vip as VipModel;
use app\vip\model\Innermsg as InnermsgModel;
use think\Db;
use think\Hook; 



class Innermsg extends Admin{

		public function index(){
			$map = $this->getMap();
	        $data_list = Db::view('vip_innermsg o', true)
            ->view("vip v", 'vip_name', 'o.vip_id=v.id', 'left')
            ->where($map)
            ->order("o.id desc")
            ->paginate();
	       
	        return ZBuilder::make('table')
	         ->setTableName('vip_innermsg') // 设置数据表名
	         ->setPageTitle('站内信列表')
	         ->setSearch(['vip_name'=>'用户名'])
	         ->addColumns([ // 批量添加列
		        ['id', 'ID'],
		        ['vip_name', '用户名'],
		        ['content', '内容'],
		        ['status','状态','status','', ['未读', '已读']],
		        ['send_time','发送时间','datetime'],
		        ['read_time','阅读时间','datetime'],
		        ['right_button', '操作', 'btn']
		    ])
	         ->addTopButton('add') // 添加顶部按钮
	         ->setRowList($data_list) // 设置表格数据
	         ->addRightButton('delete') // 添加编辑和删除按钮
	         ->setColumnWidth('content', 130)
	         ->fetch();

		}

		public function add(){
		
		if(request()->isPost()){
			$params = request()->param();
			$check = Db::name("vip")->where("vip_name",$params['vip_name'])->value("id");
			if($check < 1){
				$this->error("用户名输入不正确，请重新输入");
			}
			$params['vip_id'] = $check;
			unset($params['vip_name']);
			$params['send_time'] = time();
			$res = Db::name("vip_innermsg")->insert($params);
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
			            'name' => 'vip_name',
			            'title' => '用户名'
			          ],
			          [
						'type' => 'textarea',
			            'name' => 'content',
			            'title' => '内容'
			          ],

			      ]
			    )
			    ->fetch();
		}
		
	}

}