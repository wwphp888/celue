<?php
// +----------------------------------------------------------------------
// | 股票策略系统 [ V1.02 ]
// +----------------------------------------------------------------------
// | 版权所有 2018~2022 山东软淘电子商务有限公司 [ http://www.sdruantao.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.sdruantao.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 模块信息
 */
return [
  'name' => 'vip',
  'title' => '会员',
  'identifier' => 'vip.ming.module',
  'icon' => 'fa fa-fw fa-users',
  'description' => '会员模块',
  'author' => 'Sunxiaochuan',
  'author_url' => '',
  'version' => '1.0.0',
  'need_module' => [
    [
      'admin',
      'admin.dolphinphp.module',
      '1.0.0',
    ],
  ],
  'need_plugin' => [],
  'tables' => [
    'vip',
    'vip_bank',
    'vip_recharge',
    'vip_withdraw',
    'vip_record',
  ],
  'database_prefix' => 'tsp_',
  'config' => [
    [
      'radio',
      'vip_register',
      '会员注册',
      '',
      [
        '开启',
        '关闭',
      ],
      1,
    ],
    [
      'radio',
      'vip_login',
      '会员登录',
      '',
      [
        '开启',
        '关闭',
      ],
      1,
    ],
  ],
  'action' => [
    [
      'module' => 'vip',
      'name' => 'vip_edit',
      'title' => '修改会员信息',
      'remark' => '修改会员信息',
      'rule' => '',
      'log' => '[user|get_nickname] 修改会员信息[details]',
      'status' => 1,
    ],
    [
      'module' => 'vip',
      'name' => 'vip_delete',
      'title' => '删除会员',
      'remark' => '删除会员',
      'rule' => '',
      'log' => '[user|get_nickname] 删除会员[details]',
      'status' => 1,
    ],
  ],
];
