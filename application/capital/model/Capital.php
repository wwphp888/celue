<?php

namespace app\capital\model;

use think\Model as ThinkModel;

/**
 * 股票交易模型
 * @package app\capital\model
 */
class Capital extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__TRADE_ORDER__';
   
      
     //点买列表
    public static function getlist($map,$order)
    {
        //$where['o.status'] = intval(0);
        $type = config("MEONEY_TYPE");
        $data_list = self::view('vip_record r', true)
            ->view("vip v", 'vip_name,vip_phone', 'r.record_vip=v.id', 'left')
            ->where($map)
            ->order($order)
            ->paginate()
            ->each( function($item, $key) use ($type){
                $item['type'] = $type[$item['type']];
            });
          
        return $data_list;
    }

    public static function getfeelist($map,$order){
        $type = config("MEONEY_TYPE");

        $data_list = self::view('vip_record r', true)
            ->view("vip v", 'vip_name,vip_phone,recommendCode', 'r.record_vip=v.id', 'left')
            ->where($map)
            ->where("type",'in','6,13')
            ->order($order)
            ->paginate()
            ->each( function($item, $key) use ($type){
                $item['type'] = $type[$item['type']];
            });
        
          
        return $data_list;
    }
    public static function agent_record($map,$order)
    {
        //$where['o.status'] = intval(0);
        $type = config("MEONEY_TYPE");
        $data_list = self::view('agent_record r', true)
            ->view("agent v", 'agent_username,agent_code', 'r.agent_id=v.id', 'left')
            ->where($map)
            ->order($order)
            ->paginate()
            ->each( function($item, $key) use ($type){
                $item['type'] = $type[$item['type']];
            });
          
        return $data_list;
    }
 
}