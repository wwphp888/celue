<?php

namespace app\trade\model;

use think\Model as ThinkModel;

/**
 * 股票列表模型
 * @package app\gupiao\model
 */
class Gupiao extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__GUPIAO_LIST__';

     //点买列表
    public static function getgupiaolist($map,$order)
    {
      
        //$where['o.status'] = intval(0);
        $data_list = self::name('gupiao_list')->where($map)
            ->order($order)
            ->paginate();
           
           /* ->each( function($item, $key){
            });*/
          
        return $data_list;
    }
 
}