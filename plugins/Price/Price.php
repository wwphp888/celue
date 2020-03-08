<?php
namespace plugins\Price;


use app\common\controller\Plugin;


class Price extends Plugin{

	public $info = [
        // 插件名[必填]
        'name'        => 'Price',
        // 插件标题[必填]
        'title'       => '价格,更新',
        // 插件唯一标识[必填],格式：插件名.开发者标识.plugin
        'identifier'  => 'price.ming.plugin',
        // 插件作者[必填]
        'author'      => 'Alg',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0'
    ];


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