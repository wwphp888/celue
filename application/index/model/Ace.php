<?php

namespace app\index\model;

use think\Model as ThinkModel;
use think\Db;
/**
 * 股票交易模型
 * @package app\Invest\model
 */
class Ace extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__TRADE_ORDER__';


}