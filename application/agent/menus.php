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
 * 菜单信息
 */
return [
  [
    'title' => '代理商',
    'icon' => 'fa fa-fw fa-male',
    'url_type' => 'module_admin',
    'url_value' => 'agent/index/index',
    'url_target' => '_self',
    'online_hide' => 0,
    'sort' => 100,
    'status' => 1,
    'child' => [
      [
        'title' => '代理商管理',
        'icon' => 'fa fa-fw fa-blind',
        'url_type' => 'module_admin',
        'url_value' => '',
        'url_target' => '_self',
        'online_hide' => 0,
        'sort' => 100,
        'status' => 1,
        'child' => [
          [
            'title' => '代理商列表',
            'icon' => 'fa fa-fw fa-wheelchair-alt',
            'url_type' => 'module_admin',
            'url_value' => 'agent/index/index',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
            'child' => [
              [
                'title' => '添加代理商',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'agent/index/add',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '修改代理商',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'agent/index/edit',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];
