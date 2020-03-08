<?php 

namespace app\capital\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\capital\model\Capital as CapitalModel;
use think\Db;
use think\Hook; 
use think\Cache;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class All extends admin{

	/* 点买列表
	*/
	public function index(){
		  // 查询
	//会员数量
	$count = Db::name("vip")->count("id");
	//账户余额
	$vip_money = Db::name("vip")->SUM("vip_money");
	//累计充值
	$recharge = Db::name("vip_recharge")->where("recharge_status","in","1,3")->SUM("recharge_amount");
	//累计提现
	$withdraw = Db::name("vip_withdraw")->where("withdraw_status",1)->SUM("withdraw_amount");
	//今日新增会员数量
	$now_count = Db::name("vip")->where("register_time",">",strtotime(date('Y-m-d',time())))->count('id');
	//今日充值总额
	$now_recharge = Db::name("vip_recharge")->where("recharge_status","in","1,3")->where("recharge_time",">",strtotime(date('Y-m-d',time())))->SUM("recharge_amount");
	//今日提现总额
	$now_withdraw = Db::name("vip_withdraw")->where("withdraw_status",1)->where("withdraw_time",">",strtotime(date('Y-m-d',time())))->SUM("withdraw_amount");

	//今日新增策略数
	$now_celue = Db::name("trade_order")->where("creat_time",">",strtotime(date('Y-m-d',time())))->count('id');
	//今日新增持仓市值
	$now_ccmoney = Db::name("trade_order")->where("creat_time",">",strtotime(date('Y-m-d',time())))->SUM('trush_number*now_price');
	//今日新增信用金总额
	$now_credit = Db::name("trade_order")->where("creat_time",">",strtotime(date('Y-m-d',time())))->SUM('credit_money');
	//买入委托中策略总数
	$trush_count =  Db::name("trade_order")->where("status",1)->count('id');
	//持仓中策略总数
	$cc_count =  Db::name("trade_order")->where("status",2)->count('id');
	//已平仓策略总数
	$old_count =  Db::name("trade_order")->where("status",4)->count('id');
	//总持仓市值
	$cc_money =  Db::name("trade_order")->where("status",2)->SUM('trush_number*now_price');
	//浮动盈亏
	 $fl_list = Db::name("trade_order")->where("status",2)->select();
       $flnum = 0;
       foreach ($fl_list as $ke => $va) {
           $flnum += round(($va['now_price']-$va['trush_price'])*$va['trush_number'],2);
       }
	$float_money =  $flnum;
	//总盈亏(不包含持仓中)
	$all_money =  Db::name("trade_order")->where("status",4)->SUM('repay_profits');
	//服务费总额
	$server_money = Db::name("trade_order")->where("status","in","2,3,4")->SUM("service_money");
	//服务费总额
	$defer_money = Db::name("trade_order")->where("status","in","2,3,4")->SUM("defer_money");

$user_html = <<<EOF
	<div class="col-md-4">
    <div class="block block-bordered">
        <div class="block-header bg-gray-lighter">
            <h3 class="block-title">会员统计</h3>
        </div>
        <div class="block-content">
            <table class="table">
                <tbody>
                <tr>
                    <td>会员数量</td>
                    <td>$count 位</td>
                </tr>
                <tr>
                    <td>账户余额</td>
                    <td>$vip_money 元</td>
                </tr>
                <tr>
                    <td>累计充值</td>
                    <td>$recharge 元</td>
                </tr>
                <tr>
                    <td>累计提现</td>
                    <td>$withdraw 元</td>
                </tr>
              
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="block block-bordered">
        <div class="block-header bg-gray-lighter">
            <h3 class="block-title">今日新增</h3>
        </div>
        <div class="block-content">
            <table class="table">
                <tbody>
                <tr>
                    <td>今日新增会员数量</td>
                    <td>$now_count 位</td>
                </tr>
                <tr>
                    <td>今日充值总额</td>
                    <td>$now_recharge 元</td>
                </tr>
                <tr>
                    <td>今日提现总额</td>
                    <td>$now_withdraw 元</td>
                </tr>
                <tr>
                    <td>今日新增策略数</td>
                    <td>$now_celue 个</td>
                </tr>
                <tr>
                    <td>今日新增持仓市值</td>
                    <td>$now_ccmoney 元</td>
                </tr>
                <tr>
                    <td>今日新增信用金总额</td>
                    <td>$now_credit 元</td>
                </tr>
              
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="block block-bordered">
        <div class="block-header bg-gray-lighter">
            <h3 class="block-title">策略统计</h3>
        </div>
        <div class="block-content">
            <table class="table">
                <tbody>
                <tr>
                    <td>买入委托中策略总数</td>
                    <td>$trush_count 个</td>
                </tr>
                <tr>
                    <td>持仓中策略总数</td>
                    <td>$cc_count 个</td>
                </tr>
                <tr>
                    <td>已平仓策略总数</td>
                    <td>$old_count 个</td>
                </tr>
                <tr>
                    <td>总持仓市值</td>
                    <td>$cc_money 元</td>
                </tr>
                <tr>
                    <td>浮动总盈亏</td>
                    <td>$float_money 元</td>
                </tr>
              	<tr>
                    <td>历史总盈亏(不包含持仓中)</td>
                    <td>$all_money 元</td>
                </tr>
                <tr>
                    <td>服务费总额</td>
                    <td>$server_money 元</td>
                </tr>
                <tr>
                    <td>递延费总额</td>
                    <td>$defer_money 元</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
EOF;
		return ZBuilder::make('table')
				->setExtraHtml($user_html, 'toolbar_top')
				->fetch();

	}
}