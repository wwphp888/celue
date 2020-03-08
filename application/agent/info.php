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
  'name' => 'agent',
  'title' => '代理商',
  'identifier' => 'agent.ming.module',
  'icon' => 'fa fa-fw fa-users',
  'description' => 'agent模块',
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
    'agent',
    'agent_withdraw',
    'agent_record',
  ],
  'database_prefix' => 'tsp_',
  'config' => [],
  'action' => [],
];
