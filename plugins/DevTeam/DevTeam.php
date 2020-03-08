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

namespace plugins\DevTeam;

use app\common\controller\Plugin;
use think\Db;
/**
 * 系统环境信息插件
 * @package plugins\DevTeam
 * @author 蔡伟明 <314013107@qq.com>
 */
class DevTeam extends Plugin
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'DevTeam',
        // 插件标题[必填]
        'title'       => '代办事项',
        // 插件唯一标识[必填],格式：插件名.开发者标识.plugin
        'identifier'  => 'dev_team.ming.plugin',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-users',
        // 插件描述[选填]
        'description' => '在后台首页显示代办事项',
        // 插件作者[必填]
        'author'      => '张三',
        // 作者主页[选填]
        'author_url'  => 'http://www.sdruantao.com',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
    ];

    /**
     * @var array 插件钩子
     */
    public $hooks = [
        'admin_index'
    ];

    /**
     * 后台首页钩子
     * @author 蔡伟明 <314013107@qq.com>
     */
     public function adminIndex()
    {
        $config = $this->getConfigValue();
        //提现待审核
        $data['withdraw_count'] = Db::name("vip_withdraw")->where("withdraw_status",0)->count();
        //充值待审核
        $data['charge_count'] = Db::name("vip_recharge")->where("recharge_type",2)->where("recharge_status",0)->count();
        //建仓委托中订单
        $data['jiancang_count'] = Db::name("trade_order")->where("status",1)->count();
        //点卖委托中订单
        $data['dianmai_count'] = Db::name("trade_order")->where("status",3)->count();
        $this->assign("data",$data);
        if ($config['display']) {
            $this->fetch('widget', $config);
        }
    }

    /**
     * 安装方法
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法必
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool
     */
    public function uninstall(){
        return true;
    }
}