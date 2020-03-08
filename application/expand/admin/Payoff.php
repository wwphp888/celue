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
class Payoff extends admin{
	public function index(){
		// 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = Db::name("payoff_conf")->where($map)->order($order)->select();
       // var_dump($data_list);die;
     
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('payoff_conf')
        	->hideCheckbox()
            ->addColumns([ // 批量添加数据列
            	['id', 'ID'],
            	['title', '标题'],
                ['bankname', '银行名称'],
                ['number', '账号'],
                ['name', '收款账户名称'],
                ['bank_address', '开户行'],
                ['img', '二维码','picture'],
                ['status', '状态', 'status', '', ['禁用:danger', '启用:success']],
                ['add_time', '添加时间','datetime'],
                ['update_time', '添加时间','datetime'],
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
			$params['update_time'] = time();
			$res = Db::name("payoff_conf")->insert($params);
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
			            'name' => 'title',
			            'title' => '标题',
			            'tips' =>'例如：对公账户,支付宝等'
			          ],

			      ]
			    )
			    ->addRadio('type', '类型', '', ['0' => '银行卡', '1' => '二维码'],'0')
			    ->setFormItems(
			      [
			          [
						'type' => 'text',
			            'name' => 'bankname',
			            'title' => '银行名称',
			            'tips' =>'例如：工商银行等(二维码类型不必填写)'
			          ],
			          [
						'type' => 'text',
			            'name' => 'number',
			            'title' => '账号',
			            'tips' =>'(必填)'
			          ],
			          [
						'type' => 'text',
			            'name' => 'name',
			            'title' => '收款账户名称',
			            'tips' =>'例如：XXXX有限公司，法人姓名等(二维码类型不必填写)'
			          ],
			          [
						'type' => 'text',
			            'name' => 'bank_address',
			            'title' => '开户行',
			            'tips' =>'例如：XX省XX市XX路XX行(二维码类型不必填写)'
			          ],

			      ]
			    )
			    ->addImage('img', '二维码上传')
			    ->addRadio('status', '是否启用', '', ['1' => '启用', '0' => '禁用'],'1')
			    ->fetch();
		}
		
	}
}