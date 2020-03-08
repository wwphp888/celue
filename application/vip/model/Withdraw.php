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

namespace app\vip\model;

use think\Model;
use think\Db;

class Withdraw extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__VIP_WITHDRAW__';

    // 自动写入时间戳
   /* protected $autoWriteTimestamp = 'timestrap';
    protected $createTime = 'register_time';
    protected $updateTime = false;*/
   // protected $readonly = ['register_time'];
    // 对密码进行加密
    /*public function setVipPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }

    // 获取注册ip
    public function setVipPaypasswordAttr($value)
    {
        return Hash::make((string)$value);
    }*/

        public function getRechargeTypeAttr($value)
    {
        $status = [1=>'线上支付',2=>'线下支付'];
        return $status[$value];
    }
        public function vip()
    {
        return $this->belongsTo('Vip','withdraw_vip');
    }
 }