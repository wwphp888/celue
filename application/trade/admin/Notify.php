<?php 

namespace app\trade\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;
use think\Hook; 
use think\Cache;
use think\Request;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class Notify extends admin{

	public function index(){
		// 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = Db::name("notify")->where($map)->order($order)->paginate();
		// 使用ZBuilder快速创建数据表格
$bind_title = <<<JS
<script>
$(".builder-table-body tr").each(function(){
    $(this).find('td').eq(2).attr('title',$(this).find('td').eq(2).text());
})
</script>
JS;
        // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
        return ZBuilder::make('table')
        	->setTableName('notify')
        	->hideCheckbox()
            ->addOrder('id,type,status,add_time')
        	->setSearchArea([['text', 'info', '通知内容搜索', 'like'],])
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['type', '类型'],
                ['info', '内容'],
                ['status', '状态','status','', ['未处理', '已处理']],
                ['add_time', '委托时间','datetime'],
            ])
            ->setColumnWidth('add_time', 150)
            ->setColumnWidth('info', 550)
            ->setRowList($data_list) // 设置表格数据
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setExtraJs($bind_title)
            ->fetch(); // 渲染模板
	}

     public function export(){
       // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = Db::name("notify")->where($map)->order($order)->paginate();
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['add_time'] = date("Y-m-d H:i:s",$value['add_time']);
            $data_list[$key]['status'] = $value['status'] == '0'?'未处理':'已处理';
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['id','auto', 'ID'],
            ['id','auto', 'ID'],
            ['type','auto', '类型'],
            ['info','auto', '内容'],
            ['status','auto', '状态'],
            ['add_time','auto', '委托时间'],

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['实盘通知记录列表', $cellName, $data_list]);
    }
}