<?php
/**
 * 模块信息
 */
return [
    // 模块名[必填]
    'name'        => 'trade',
    // 模块标题[必填]
    'title'       => '股票交易',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'trade.tsp.module',
     //图标
    'ico' => 'fa fa-fw fa-line-chart',
    // 模块描述[必填] 
	'description' => '股票交易模块',
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
    'tables'=> [
        'trade_order', //交易订单表
        ],
    //配置参数
    'config' => [
         ['text', 'trade_name', '证券名称', "调用方法：config('tradex_name')"],
         ['text', 'trade_number', '证券账号', "调用方法：config('trade_number')"],
         ['radio', 'stockinfos', '数据源', '', ['1' => '实盘', '0' => '新浪'], 1],
         ['text', 'profit_proportion', '盈利分成', "调用方法：config('profit_proportion')"],
         ['text', 'strategy_credit_rec', '信用金推荐', "信用金推荐用于显示前台推荐按钮金额，以“|”间隔，调用方法：config('strategy_credit_rec')"],
         ['text', 'strategy_rate', '信用金倍率', "信用金倍率用于计算购买最大股票上线金额。用英文“|”间隔，最多四个倍率，调用方法：config('strategy_rate')"],
         ['text', 'strategy_fee', '交易综合费（元/万）', "交易综合费 交易本金的费率，包含第一天费用和第二天的递延费， 调用方法：config('strategy_fee')"],
         ['text', 'strategy_renewal_fee', '续期服务费（元/万）', "自动续期服务费 ，调用方法：config('strategy_renewal_fee')"],
         ['text', 'winstop', '止盈比例', "调用方法：config('winstop')"],
         ['text', 'downstop', '止损比例', "调用方法：config('downstop')"],
        ],
    //授权参数
    'access' => [
            'group' => [
                "tab_title"   => '股票交易授权',
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