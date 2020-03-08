<?php
/**
 * 模块信息
 */
return [
    // 模块名[必填]
    'name'        => 'capital',
    // 模块标题[必填]
    'title'       => '资金统计',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'capital.tsp.module',
     //图标
    'ico' => 'fa fa-fw fa-line-chart',
    // 模块描述[必填] 
	'description' => '资金统计模块',
    // 开发者[必填]
    'author'      => 'admin001',
    //开发者网址：sdruantao.com
    'author_url' =>'http://www.sdruantao.com',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    'version'     => '1.0.0',
    //模块依赖 
    'need_module' => '',  
    //插件依赖
    'need_plugin' =>'',
    //数据表
  /*  'tables'=> [
        '', //
        ],*/
    //配置参数
    'config' => [
         ['text', 'appid', 'AppId', '应用ID，登录 微信公众平台 查看'],
        ],
    //授权参数
    'access' => [
            'group' => [
                "tab_title"   => '资金统计授权',
                "table_name"  => "admin_group",
                "primary_key" => "id",
                "parent_id"   => "pid",
                "node_name"   => "name"
            ],
        ],
    //行为配置
     'action' => [
            [
                'module' => 'cms',
                'title'  => '添加文章',
                'remark' => '添加文章',
                'name'   => 'article_add',
                'log'    => '用户：[user|get_nickname] 在[time|format_time]添加了文章'
            ],
            [
                'module' => 'cms',
                'name'   => 'article_delete',
                'title'  => '删除文章',
                'remark' => '删除文章',
                'log'    => '用户：[user|get_nickname] 在[time|format_time]删除了文章',
                'status' => 1
            ],
        ],

   
];