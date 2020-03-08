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
    'title' => '会员中心',
    'icon' => 'fa fa-fw fa-group',
    'url_type' => 'module_admin',
    'url_value' => 'vip/index/index',
    'url_target' => '_self',
    'online_hide' => 0,
    'sort' => 100,
    'status' => 1,
    'child' => [
      [
        'title' => '会员管理',
        'icon' => 'fa fa-fw fa-male',
        'url_type' => 'module_admin',
        'url_value' => '',
        'url_target' => '_self',
        'online_hide' => 0,
        'sort' => 100,
        'status' => 1,
        'child' => [
          [
            'title' => '会员列表',
            'icon' => 'fa fa-fw fa-users',
            'url_type' => 'module_admin',
            'url_value' => 'vip/index/index',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
            'child' => [
              [
                'title' => '添加会员',
                'icon' => 'fa fa-fw fa-plus',
                'url_type' => 'module_admin',
                'url_value' => 'vip/index/add',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '修改会员信息',
                'icon' => 'fa fa-fw fa-comment',
                'url_type' => 'module_admin',
                'url_value' => 'vip/index/edit',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
            ],
          ],
          [
            'title' => '充值列表',
            'icon' => 'fa fa-fw fa-file-text-o',
            'url_type' => 'module_admin',
            'url_value' => 'vip/recharge/index',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
            'child' => [
              [
                'title' => '线上充值',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'vip/recharge/online',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '线下充值',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'vip/recharge/off',
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
