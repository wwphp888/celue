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
class Fee extends admin{

	/* 点买列表
	*/
	public function index(){
		   // 查询
       $map = $this->getMap();

        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CapitalModel::getfeelist($map,$order);
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
            ->setSearchArea([['text', 'recommendCode', '代理商编码'],['text', 'vip_name', '用户名(选填)'],])
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
            ->setRowList($data_list) // 设置表格数据
            ->setExtraJs($bind_title)
            ->fetch(); // 渲染模板

	}
}