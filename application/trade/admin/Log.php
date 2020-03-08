<?php 

namespace app\trade\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\trade\model\Log as LogModel;
use think\Db;
use think\Hook; 
use think\Cache;
use think\Request;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class Log extends admin{

	public function index(){
		// 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = LogModel::getloglist($map,$order);
         // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('trade_log')
        	->hideCheckbox()
          //  ->setSearch(['title' => '订单号','vip_phone'=>'手机号'],'','',true) // 设置搜索框
        	->setSearchArea([['text', 'vip_name', '用户名'],['text', 'trade_order', '订单号'],])
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['vip_name', '用户名'],
                ['trade_order', '订单号'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['log', '操作信息'],
                ['add_time', '委托时间','datetime'],
            ])
            ->setColumnWidth('trade_order,add_time', 150)
            ->setColumnWidth('log', 550)
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}
      public function export(){
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = LogModel::getloglist($map,$order);
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['add_time'] = date("Y-m-d H:i:s",$value['add_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id','auto', 'ID'],
            ['vip_name','auto', '用户名'],
            ['trade_order','auto', '订单号'],
            ['gupiao_name','auto', '股票名称'],
            ['gupiao_code','auto', '股票代码'],
            ['log','auto', '操作信息'],
            ['add_time','auto', '委托时间'],

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['操作记录列表', $cellName, $data_list]);
    }
}