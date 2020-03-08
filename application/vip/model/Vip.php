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
use think\helper\Hash;
use think\Db;

class Vip extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__VIP__';

    // 自动写入时间戳
     protected $autoWriteTimestamp = false;
    //protected $autoWriteTimestamp = 'timestrap';
    protected $createTime = 'register_time';
    protected $updateTime = false;
   // protected $readonly = ['register_time'];
    // 对密码进行加密
    public function setVipPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }

    // 获取注册ip
    public function setVipPaypasswordAttr($value)
    {
        return Hash::make((string)$value);
    }
        public function recharge()
    {
        return $this->hasMany('Recharge','recharge_vip');
    }
 }