<?php 

namespace app\capital\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\capital\model\Capital as CapitalModel;
use think\Db;
use think\Hook; 
use think\Cache;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class Index extends admin{

	/* 点买列表
	*/
	public function index(){
		  // 查询
       $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CapitalModel::getlist($map,$order);
     // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
         $type = config("MEONEY_TYPE");
         $bind_title = <<<JS
<script>
$(".builder-table-body tr").each(function(){
    $(this).find('td').eq(6).attr('title',$(this).find('td').eq(6).text());
})
</script>
JS;
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('vip_record')
        	->hideCheckbox()
        	->setSearchArea([['text', 'vip_name', '用户名'],['text', 'vip_phone', '手机号'],['select', 'type', '操作类型', '', 'test', $type],])
             ->addTimeFilter('record_time')
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['vip_phone', '手机号'],
                ['vip_name', '用户名'],
                ['type', '操作类型'],
                ['record_affect', '影响金额'],
                ['record_money', '可用金额'],
                ['record_info', '备注'],
                ['record_time', '发生时间','datetime']
               
            ])
            ->setColumnWidth('order_no,vip_phone', 130)
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->setExtraJs($bind_title)
            ->fetch(); // 渲染模板
	}
  public function export(){
     // 查询
       $map = $this->getMap();
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CapitalModel::getlist($map,$order);
        foreach ($data_list as $key => $value) {
            $data_list[$key]['record_time'] = date("Y-m-d H:i:s",$value['record_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['vip_name', 'auto', '用户名'],
            ['vip_phone', 'auto', '手机号'],
            ['type', 'auto', '操作类型'],
            ['record_affect', 'auto', '影响金额'],
            ['record_money', 'auto', '可用金额'],
            ['record_info', 'auto', '备注'],
            ['record_time', 'auto', '创建时间']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['资金明细列表', $cellName, $data_list]);
    }
   public function agent_record(){
      // 查询
       $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CapitalModel::agent_record($map,$order);
    // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('agent_export',http_build_query($this->request->param()))
        ];
    // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
          ->setTableName('agent_record')
          ->hideCheckbox()
          ->setSearchArea([['text', 'agent_username', '用户名'],['text', 'agent_code', '机构码'],])
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['agent_code', '机构码'],
                ['agent_username', '用户名'],
                ['type', '操作类型'],
                ['affect_money', '影响金额'],
                ['agent_money', '可用金额'],
                ['record_info', '备注'],
                ['recod_time', '发生时间','datetime']
               
            ])
             ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
  }
  public function agent_export(){
     // 查询
       $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CapitalModel::agent_record($map,$order);
        foreach ($data_list as $key => $value) {
            $data_list[$key]['recod_time'] = date("Y-m-d H:i:s",$value['recod_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id', 'auto', 'ID'],
            ['agent_code', 'auto', '机构码'],
            ['agent_username', 'auto', '用户名'],
            ['type', 'auto', '操作类型'],
            ['affect_money', 'auto', '影响金额'],
            ['agent_money', 'auto', '可用金额'],
            ['record_info', 'auto', '备注'],
            ['recod_time', 'auto', '创建时间']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['代理商资金记录列表', $cellName, $data_list]);
    }

}