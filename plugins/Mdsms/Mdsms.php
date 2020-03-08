<?php
namespace plugins\Mdsms;

use app\common\controller\Plugin;

/**
 * 演示插件
 */
class Mdsms extends Plugin
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'Mdsms',
        // 插件标题[必填]
        'title'       => '通讯组件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.plugin
        'identifier'  => 'mdsms.tsp.plugin',
        // 插件作者[必填]
        'author'      => 'admin001',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        //插件描述
        'description' => '漫道短信接口',
        //后台管理功能开启
        'admin' => '1',
    ];

    /**
     * @var string 原数据库表前缀
     */
   // public $database_prefix = 'tsp_';
    
    /**
     * @var array 管理界面字段信息
     */
    public $admin = [
        'title'        => '漫道短信发送记录', // 后台管理标题
        'table_name'   => 'mdsms', // 数据库表名，如果没有用到数据库，则留空
        'order'        => 'create_time', // 需要排序功能的字段，多个字段用逗号隔开
        'filter'       => 'phone', // 需要筛选功能的字段，多个字段用逗号隔开
        'search_title' => '', // 搜索框提示文字,一般不用填写
        'search_field' => [ // 需要搜索的字段，如果需要搜索，则必填，否则不填
            'phone' => '手机号'
        ],
        
        // 后台列表字段
        'columns' => [
             ['id', 'ID'],
             ['phone', '手机号'],
             ['code', '验证码'],
             ['info', '内容'],
             ['status', '状态',['失败', '成功']],
             ['create_time', '发送时间', 'datetime'],
        ],
        
        // 右侧按钮
        'right_buttons' => [],
        
        // 顶部栏按钮
        'top_buttons' => []
    ];
    /**
     * 安装方法必须实现
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法必须实现
     */
    public function uninstall(){
        return true;
    }
}