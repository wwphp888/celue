<?php

namespace app\trade\model;

use think\Model as ThinkModel;

/**
 * 股票交易模型
 * @package app\trade\model
 */
class Trade extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__TRADE_ORDER__';

     //点买列表
    public static function getbuylist($map,$order)
    {
        //$where['o.status'] = intval(0);
        $data_list = self::view('trade_order o', true)
            ->view("vip v", 'id as user_id,vip_name,vip_phone', 'o.user_id=v.id', 'left')
            ->where($map)
            ->order($order)
            ->paginate();
           /* ->each( function($item, $key){
            });*/
          
        return $data_list;
    }
     public function getSellTypeAttr($value)
    {
        $status = [1=>'自动',2=>'手动'];
        return $status[$value];
    }
}