<?php 

namespace app\trade\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\trade\model\Trade as TradeModel;
use think\Db;
use think\Hook; 
use think\Cache;
use think\Request;

/**
 * 订单管理控制器
 * @package app\trade\idnex
 */
class Index extends admin{

	/* 点买列表
	*/
	public function index(){
		  // 查询
        $map = $this->getMap();

        $map['o.status'] = 1;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
       // var_dump($data_list);die;
         // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('index_export',http_build_query($this->request->param()))
        ];
       $btn_access = [
		    'title' => '手动查询订单状态',
		    'icon'  => 'fa fa-fw fa-key',
        'class' => 'btn btn-xs btn-default ajax-get confirm',
		    'href'  => url('manual_verify', ['id' => '__id__']),
         'data-title' => '真的要手动查询吗？',
        'data-tips' => '手动查询将请求实盘，验证该笔订单状态~'
		];
    $btn_error = [
        'title' => '手动审核失败',
        'icon'  => 'fa fa-fw fa-times-circle',
        'class' => 'btn btn-xs btn-default ajax-get confirm',
        'href'  => url('manual_verify_error', ['id' => '__id__']),
        'data-title' => '真的要手动审核吗？',
        'data-tips' => '手动审核后将退回信用金，该操作不可逆，请谨慎操作~'
    ];
    $btn_che = [
        'title' => '撤单',
        'icon'  => 'fa fa-fw fa-reply',
        'class' => 'btn btn-xs btn-default ajax-get confirm',
        'href'  => url('manual_verify_che', ['id' => '__id__']),
        'data-title' => '真的要撤单吗？',
        'data-tips' => '撤单成功后将退回信用金，该操作不可逆，请谨慎操作~'
    ];
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('trade_order')
        	->hideCheckbox()
          //  ->setSearch(['title' => '订单号','vip_phone'=>'手机号'],'','',true) // 设置搜索框
        	->setSearchArea([['text', 'order_no', '订单号'],['text', 'vip_phone', '手机号'],])
            ->addColumns([ // 批量添加数据列
                ['order_no', '订单号'],
                ['vip_phone', '手机号'],
                ['vip_name', '用户名'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['trush_price', '委托价'],
                ['trush_number', '委托数量'],
                ['now_price', '现价'],
                ['trush_no', '委托编号'],
                ['notify_id', '成交通知ID'],
                ['deal_number', '成交数量'],
                ['deal_time', '最后成交时间','datetime'],
                ['stop_win', '止盈价'],
                ['stop_down', '止损价'],
                ['credit_money', '信用金'],
                ['credit_rate', '信用倍率'],
                ['creat_time', '委托时间','datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->setColumnWidth('order_no,vip_phone,vip_name,deal_time,creat_time', 150)
            ->addRightButton('custom',$btn_access) // 批量添加右侧按钮
            ->addRightButton('custom',$btn_error) // 批量添加右侧按钮
            ->addRightButton('custom',$btn_che) // 批量添加右侧按钮
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}
    public function index_export(){
    // 查询
        $map = $this->getMap();

        $map['o.status'] = 1;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['creat_time'] = date("Y-m-d H:i:s",$value['creat_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['order_no','auto', '订单号'],
            ['vip_phone','auto', '手机号'],
            ['vip_name','auto', '用户名'],
            ['gupiao_name','auto', '股票名称'],
            ['gupiao_code', 'auto','股票代码'],
            ['trush_price', 'auto','委托价'],
            ['trush_number','auto', '委托数量'],
            ['now_price','auto', '现价'],
            ['trush_no', 'auto','委托编号'],
            ['notify_id', 'auto','成交通知ID'],
            ['deal_number', 'auto','成交数量'],
            ['deal_time', 'auto','最后成交时间'],
            ['stop_win','auto', '止盈价'],
            ['stop_down', 'auto','止损价'],
            ['credit_money','auto', '信用金'],
            ['credit_rate','auto', '信用倍率'],
            ['creat_time', 'auto','委托时间']

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['点买委托列表', $cellName, $data_list]);
    }
/*手动审核点买（成功）
*/
public function manual_verify(){
      $id = request()->param('id');
        if($id < 1){
            $this->error("数据错误，请重试");
        }
      $orderinfo = Db::name("trade_order")->where("id",$id)->find();
      if($orderinfo['trush_no'] < 1){
        $this->error("该订单无委托编号，请核实后按委托失败处理");
      }
      //查询当日成交
      $parm = '{"req":"Trade_QueryData","rid":"5","para":{"JsonType" : 0,"QueryType" : 3}}';
      $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($parm));
      if(empty($res) || $res == false){
           $this->error('交易服务通讯失败，请联系管理员处理');
        }
      $checkres = json_decode($res,true);
      if(isset($checkres['data']['ErrInfo'])){
          $this->error($checkres['data']['ErrInfo']);
      }
      if(!is_array($checkres['data'])){
        $this->error("查询成功，该笔订单无完成交易记录");
      }
      foreach ($checkres['data'] as $key => $value) {
        if($value['委托编号'] == $orderinfo['trush_no'] && $value['证券代码'] == $orderinfo['gupiao_code'] && $value['买卖标志'] == '0'){
            $dede['deal_number'] = $value['成交数量'];
            $dede['deal_time'] = time();
            if($orderinfo['trush_number'] == $dede['deal_number']){
              $dede['status'] =2;//持仓中
            }else{
              $dede['status'] =1;//委托交易中
            }
            $res = Db::name("trade_order")->where("id",$id)->update($dede);
            if($res){
               settrade_log($id,"委托买入订单，手动查询成功，订单转为持仓中成功");
               $this->success('查询成功，该笔订单已交易完成，该订单转为持仓中成功');
            }else{
              $this->error("查询成功，该笔订单已交易完成，该订单转为持仓中失败，请重试");
            }
        }
      }
       $this->error("查询成功，该笔订单无完成交易记录");
     
     
}
//手动审核失败
public function manual_verify_error(){
  $id = request()->param('id');
        if($id < 1){
            $this->error("数据错误，请重试");
        }
      $orderinfo = Db::name("trade_order")->where("id",$id)->find();
      $dede['deal_time'] = time();
      $dede['status'] =5;//手动审核失败
      $res = Db::name("trade_order")->where("id",$id)->update($dede);
      money_log($orderinfo['credit_money'],$orderinfo['user_id'],16,"委托订单审核失败，信用金退回");
      if($res){
        settrade_log($id,"委托买入订单，手动审核为失败");
         $this->success('手动审核成功');
      }else{
        $this->error("手动审核失败");
      }
}
//撤单
public function manual_verify_che(){
   $this->error("该功能暂未启用");
   $id = request()->param('id');
        if($id < 1){
            $this->error("数据错误，请重试");
        }
      $orderinfo = Db::name("trade_order")->where("id",$id)->find();
      //撤单查询
      $parm = '{"req":"Trade_QueryData","rid":"5","para":{"JsonType" : 0,"QueryType" : 5}}';
      $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($parm));
      if(empty($res) || $res == false){
           $this->error('交易服务通讯失败，请联系管理员处理');
        }
      $checkres = json_decode($res,true);
      if(isset($checkres['data']['ErrInfo'])){
          $this->error($checkres['data']['ErrInfo']);
      }
      if(!is_array($checkres['data'])){
        $this->error("查询成功，该笔订单无完成交易记录");
      }


      $dede['deal_time'] = time();
      $dede['status'] =5;//手动审核失败
      $res = Db::name("trade_order")->where("id",$id)->update($dede);
      money_log($orderinfo['credit_money'],$orderinfo['user_id'],16,"委托订单审核失败，信用金退回");
      if($res){
        settrade_log($id,"委托买入订单，手动审核为失败");
         $this->success('手动审核成功');
      }else{
        $this->error("手动审核失败");
      }
}
	/*策略持仓
	*/
	public function position(){
		  // 查询
        $map = $this->getMap();

        $map['o.status'] = 2;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
       // var_dump($data_list);die;
         // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',http_build_query($this->request->param()))
        ];
       $btn_access = [
		    'title' => '强制平仓',
		    'icon'  => 'fa fa-fw fa-product-hunt',
		    'class' => 'btn btn-xs btn-default ajax-get confirm',
		    'href'  => url('tostop', ['id' => '__id__']),
		    'data-title' => '真的要强制平仓吗？',
    		'data-tips' => '强制平仓操作不可逆，请谨慎操作~'
		];
     $btn_error = [
        'title' => '手动平仓',
        'icon'  => 'fa fa-fw fa-times-circle',
        'class' => 'btn btn-xs btn-default ajax-get confirm',
        'href'  => url('stop_verify_error', ['id' => '__id__']),
        'data-title' => '真的要手动平仓吗？',
        'data-tips' => '手动平仓将不通知服务器进行实盘交易(直接显示已全部卖出并按照当前价格进行结算)，该操作不可逆，请谨慎操作~'
    ];
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('trade_order')
        	->hideCheckbox()
            ->setSearchArea([['text', 'order_no', '订单号'],['text', 'vip_phone', '手机号'],])
            ->addColumns([ // 批量添加数据列
                ['order_no', '订单号'],
                ['buy_type', '实盘状态', 'status', '', ['实盘:success', '模拟:danger']],
                ['vip_phone', '手机号'],
                ['vip_name', '用户名'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['trush_price', '委托价'],
                ['trush_number', '数量'],
                ['now_price', '现价'],
                ['stop_win', '止盈价'],
                ['stop_down', '止损价'],
                ['credit_money', '信用金'],
                ['credit_rate', '信用倍率'],
                ['creat_time', '委托时间','datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->setColumnWidth('order_no,vip_phone,vip_name,creat_time', 150)
            ->addRightButton('btn_access',$btn_access) // 批量添加右侧按钮
            ->addRightButton('btn_error',$btn_error) // 批量添加右侧按钮
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->replaceRightButton(['buy_type' => ['eq', '1']], '', 'btn_access')
            ->replaceRightButton(['id' => ['<', 1]], '<button class="btn btn-danger btn-xs" type="button" disabled>不可操作</button>','custom')
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}
  public function export(){
    // 查询
        $map = $this->getMap();

        $map['o.status'] = 2;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['creat_time'] = date("Y-m-d H:i:s",$value['creat_time']);
            $data_list[$key]['buy_type'] = $value['buy_type'] == '1'?'实盘':'模拟';
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['order_no','auto', '订单号'],
            ['buy_type','auto', '实盘状态'],
            ['vip_phone','auto', '手机号'],
            ['vip_name','auto', '用户名'],
            ['gupiao_name','auto', '股票名称'],
            ['gupiao_code','auto', '股票代码'],
            ['trush_price', 'auto','委托价'],
            ['trush_number','auto', '数量'],
            ['now_price', 'auto','现价'],
            ['stop_win', 'auto','止盈价'],
            ['stop_down', 'auto','止损价'],
            ['credit_money','auto', '信用金'],
            ['credit_rate','auto', '信用倍率'],
            ['creat_time','auto', '委托时间']

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['持仓列表', $cellName, $data_list]);
    }
//正常卖出
    public function tostop($id=null){
    $id = request()->param('id');
        if($id < 1){
            $this->error("数据错误，请重试");
        }
        $trade_info = Db::name("trade_order")->where("id",$id)->find();

        if($trade_info['status'] != '2'){
            $this->error("该笔订单状态异常，请刷新后重新提交");
        }
        $deal_day = date("d",$trade_info['deal_time']);
        $now_day = date("d",time());
        if($deal_day == $now_day){
          $this->error("该订单明日才可进行委托卖出操作");
        }
         $EType = codefenxi($trade_info['gupiao_code']);
         $nowinfos = $this->getwudang($trade_info['gupiao_code']);
         //var_dump($nowinfos[3]);
         if(!isset($nowinfos[3])){
            $this->error('获取最新行情失败，请检查服务是否开启');
         }
        
    
          $toparms = '{"req":"Trade_CommitOrder","rid":"1008","para":[ { "Code" : "'.$trade_info['gupiao_code'].'", "Count" : '.$trade_info['deal_number'].', "EType" : '.$EType.', "OType" : 2, "PType" : 1, "Price" : "'.$nowinfos[3].'" } ] }';
          //error_log(print_r($toparms,1),3,'sell211.txt');
          $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($toparms));
          //error_log(print_r($res,1),3,'2sellsuccess.txt');
        
          $info = json_decode($res,true);
          if(isset($info['data'][0]['委托编号'])){
              if($info['data'][0]['委托编号'] < 1){
                $this->error("委托失败，请重新委托");
              }

          }
           if(isset($info['data']['ErrInfo']) || empty($res) || $res == false){
                 $this->error("卖出委托失败");
              }
         $sdata['sell_trush_no'] = $info['data'][0]['委托编号'];
         $sdata['sell_type'] = 1; //后台卖出显示为自动卖出
         $sdata['sell_price'] = $nowinfos[3];
         $sdata['sell_time'] = time();
         $sdata['sell_number'] = $trade_info['deal_number'];
         $sdata['status'] = 3; //卖出委托中
         $results = Db::name("trade_order")->where("id",$id)->update($sdata);
         if($results){
          //给代理商返佣
          $ying_repay = $trade_info['pay_service_money']+$trade_info['pay_defer_money'];
           agent_repay($trade_info['user_id'],$ying_repay);
          settrade_log($id,"订单委托卖出成功，等待底层消息回馈~");
            $this->success("卖出委托成功");
         }else{
            $this->error("卖出委托失败");
         }



  }
//手动平仓
  public function stop_verify_error(){
      $id = request()->param('id');
        if($id < 1){
            $this->error("数据错误，请重试");
        }
      $orderinfo = Db::name("trade_order")->where("id",$id)->find();
      if($orderinfo['status'] != '2'){
          $this->error("数据状态有误，请重试");
      }
      $nowinfo = sina_market_bs($orderinfo['gupiao_code']);
      if(!isset($nowinfo[3])){
        $this->error("获取最新行情失败，请重试");
      }
      if($nowinfo[3] < 0.001 || $nowinfo[3] == '' || empty($nowinfo[3])){
        $this->error("获取最新行情失败，请重试");
      }

       $sdata['sell_type'] = 1; //后台卖出显示为自动卖出
       $sdata['sell_price'] = $nowinfo[3];
       $sdata['sell_time'] = time();
       $sdata['sell_number'] = $orderinfo['deal_number'];
        $sdata['status'] = 4;//已平仓完成
       //返还信用金及收益
       $prifits = ($sdata['sell_price']-$sdata['trush_price'])*$sdata['sell_deal_number'];
       if($prifits > 0){
          $sdata['repay_creat_money'] = $orderinfo['credit_money'];
          $sdata['repay_profits'] = $prifits;
       }else{
          $sdata['repay_creat_money'] = $orderinfo['credit_money']+$prifits;
          $sdata['repay_profits'] = 0;
       }
       $res = Db::name("trade_order")->where("id",$id)->update($sdata);

       money_log($sdata['repay_creat_money']+$sdata['repay_profits'],$orderinfo['user_id'],14,"对".$orderinfo['gupiao_name']."进行卖出，返还信用金".$sdata['repay_creat_money']."元+盈利".$sdata['repay_profits']."元");
      if($res){
        //给代理商返佣
        $ying_repay = $orderinfo['pay_service_money']+$orderinfo['pay_defer_money'];
         agent_repay($orderinfo['user_id'],$ying_repay);
         settrade_log($id,"持仓中订单手动设置为已完成状态，并结算");
         $this->success('手动审核成功');
      }else{
        $this->error("手动审核失败");
      }
  }

	/*点卖委托中
	*/
	public function sell(){
		  // 查询
        $map = $this->getMap();

        $map['o.status'] = 3;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
       // var_dump($data_list);die;
          // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('sell_export',http_build_query($this->request->param()))
        ];
         $btn_access = [
        'title' => '手动查询订单状态',
        'icon'  => 'fa fa-fw fa-key',
        'class' => 'btn btn-xs btn-default ajax-get confirm',
        'href'  => url('sell_verify_success', ['id' => '__id__']),
         'data-title' => '真的要手动查询吗？',
        'data-tips' => '手动查询将请求实盘，验证该笔订单状态~'
    ];
       $btn_error = [
		    'title' => '手动审核点卖',
		    'icon'  => 'fa fa-fw fa-times-circle',
		    'class' => 'btn btn-xs btn-default ajax-get confirm',
		    'href'  => url('sell_verify_error', ['id' => '__id__']),
		     'data-title' => '真的要手动审核吗？',
        'data-tips' => '手动审核后系统将不接收该订单底层点买通知(将直接按照全部出售进行结算)，此操作不可逆，请谨慎操作~'
		];
		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setTableName('trade_order')
        	->hideCheckbox()
            ->setSearchArea([['text', 'order_no', '订单号'],['text', 'vip_phone', '手机号'],])
            ->addColumns([ // 批量添加数据列
                ['order_no', '订单号'],
                ['vip_phone', '手机号'],
                ['vip_name', '用户名'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['trush_price', '委托价'],
                ['trush_number', '数量'],
                ['now_price', '现价'],
                ['stop_win', '止盈价'],
                ['stop_down', '止损价'],
                ['credit_money', '信用金'],
                ['credit_rate', '信用倍率'],
                ['sell_type', '委托类型',],
                ['sell_price', '委托价格'],
                ['sell_number', '委托数量'],
                ['sell_deal_number', '成交数量'],
                ['sell_deal_time', '最后成交时间'],
                ['creat_time', '委托时间','datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->setColumnWidth('order_no', 150)
            ->setColumnWidth('vip_phone,vip_name,creat_time,sell_deal_time', 130)
            ->addRightButton('custom',$btn_access) // 批量添加右侧按钮
             ->addRightButton('custom',$btn_error) // 批量添加右侧按钮
             ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}

 public function sell_export(){
    // 查询
        $map = $this->getMap();

        $map['o.status'] = 3;
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['creat_time'] = date("Y-m-d H:i:s",$value['creat_time']);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['order_no','auto', '订单号'],
            ['vip_phone','auto', '手机号'],
            ['vip_name','auto', '用户名'],
            ['gupiao_name','auto', '股票名称'],
            ['gupiao_code','auto', '股票代码'],
            ['trush_price','auto', '委托价'],
            ['trush_number','auto', '数量'],
            ['now_price','auto', '现价'],
            ['stop_win','auto', '止盈价'],
            ['stop_down','auto', '止损价'],
            ['credit_money','auto', '信用金'],
            ['credit_rate','auto', '信用倍率'],
            ['sell_type','auto', '委托类型',],
            ['sell_price','auto', '委托价格'],
            ['sell_number','auto', '委托数量'],
            ['sell_deal_number','auto', '成交数量'],
            ['sell_deal_time','auto', '最后成交时间'],
            ['creat_time','auto', '委托时间'],

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['点卖委托列表', $cellName, $data_list]);
    }
 /*手动审核点卖操作
  */
  public function sell_verify_success(){
      $id = request()->param('id');
          if($id < 1){
              $this->error("数据错误，请重试");
          }
        $orderinfo = Db::name("trade_order")->where("id",$id)->find();
        if($orderinfo['status'] != '3'){
          $this->error("数据状态有误，只可审核委托卖出中的项目");
        }
        if($orderinfo['sell_price'] <= 0 || $orderinfo['sell_trush_no'] == ''){
           $this->error("出售价格错误，或委托编号不存在，请仔细核对该订单，并联系技术处理");
        }
       if($orderinfo['sell_trush_no'] < 1){
        $this->error("该订单无委托编号，请核实后按委托失败处理");
      }
      //查询当日成交
      $parm = '{"req":"Trade_QueryData","rid":"5","para":{"JsonType" : 0,"QueryType" : 3}}';
      $res = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",get_socket_info($parm));
      if(empty($res) || $res == false){
           $this->error('交易服务通讯失败，请联系管理员处理');
        }
      $checkres = json_decode($res,true);
      if(isset($checkres['data']['ErrInfo'])){
          $this->error($checkres['data']['ErrInfo']);
      }
      if(!is_array($checkres['data'])){
        $this->error("查询成功，该笔订单无完成交易记录");
      }

  foreach ($checkres['data'] as $key => $value) {
          if($value['委托编号'] == $orderinfo['sell_trush_no'] && $value['证券代码'] == $orderinfo['gupiao_code'] && $value['买卖标志'] == '1'){
                 $sdata['sell_time'] = time();
                 $sdata['sell_deal_number'] = $value['成交数量'];
                 if($orderinfo['sell_number'] == $sdata['sell_deal_number']){
                   $sdata['status'] = 4;//已平仓完成
                 }else{
                   $sdata['status'] = 3;//卖出委托交易中
                 }
                 //返还信用金及收益
                 $prifits = ($orderinfo['sell_price']-$orderinfo['trush_price'])*$sdata['sell_deal_number'];
                 if($prifits > 0){
                    $sdata['repay_creat_money'] = $orderinfo['credit_money'];
                    $sdata['repay_profits'] = $prifits;
                 }else{
                    $sdata['repay_creat_money'] = $orderinfo['credit_money']+$prifits;
                    $sdata['repay_profits'] = 0;
                 }
                 $res = Db::name("trade_order")->where("id",$id)->update($sdata);

                 money_log($sdata['repay_creat_money']+$sdata['repay_profits'],$orderinfo['user_id'],14,"对".$orderinfo['gupiao_name']."进行卖出，返还信用金".$sdata['repay_creat_money']."元+盈利".$sdata['repay_profits']."元");
                if($res){
                  settrade_log($id,"委托卖出中订单手动查询成功，设置为已完成状态，并结算");
                   $this->success('查询成功，该订单已结算成功');
                }else{
                  $this->error("查询成功，该订单结算失败");
                }
          }
        }
        $this->error("查询成功，该笔订单无完成交易记录");

         
  }
  /*手动审核点卖操作
  */
  public function sell_verify_error(){
      $id = request()->param('id');
          if($id < 1){
              $this->error("数据错误，请重试");
          }
        $orderinfo = Db::name("trade_order")->where("id",$id)->find();
        if($orderinfo['status'] != '3'){
          $this->error("数据状态有误，只可审核委托卖出中的项目");
        }
        if($orderinfo['sell_price'] <= 0 || $orderinfo['sell_trush_no'] == ''){
           $this->error("出售价格错误，或委托编号不存在，请仔细核对该订单，并联系技术处理");
        }
         $sdata['sell_time'] = time();
         $sdata['sell_number'] = $orderinfo['deal_number'];
          $sdata['status'] = 4;//已平仓完成
         //返还信用金及收益
         $prifits = ($sdata['sell_price']-$sdata['trush_price'])*$sdata['sell_deal_number'];
         if($prifits > 0){
            $sdata['repay_creat_money'] = $orderinfo['credit_money'];
            $sdata['repay_profits'] = $prifits;
         }else{
            $sdata['repay_creat_money'] = $orderinfo['credit_money']+$prifits;
            $sdata['repay_profits'] = 0;
         }
         $res = Db::name("trade_order")->where("id",$id)->update($sdata);

         money_log($sdata['repay_creat_money']+$sdata['repay_profits'],$orderinfo['user_id'],14,"对".$orderinfo['gupiao_name']."进行卖出，返还信用金".$sdata['repay_creat_money']."元+盈利".$sdata['repay_profits']."元");
        if($res){
          settrade_log($id,"委托卖出中订单手动设置为已完成状态，并结算");
           $this->success('手动审核成功');
        }else{
          $this->error("手动审核失败");
        }
  }


  /*点卖委托中
  */
  public function oldlist(){
      // 查询
        $map = $this->getMap();

        $map['o.status'] = array("in",'4,5');
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
       // var_dump($data_list);die;
     // 导出按钮
        $btn_excel = [
            'title' => '导出Excel文件',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('old_export',http_build_query($this->request->param()))
        ];
    // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
          ->setTableName('trade_order')
          ->hideCheckbox()
            ->setSearchArea([['text', 'order_no', '订单号'],['text', 'vip_phone', '手机号'],])
            ->addColumns([ // 批量添加数据列
                ['order_no', '订单号'],
                ['buy_type', '实盘状态', 'status', '', ['实盘:success', '模拟:danger']],
                ['vip_phone', '手机号'],
                ['vip_name', '用户名'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['trush_price', '委托价'],
                ['trush_number', '数量'],
                ['now_price', '现价'],
                ['stop_win', '止盈价'],
                ['stop_down', '止损价'],
                ['credit_money', '信用金'],
                ['credit_rate', '信用倍率'],
                ['sell_type', '委托类型'],
                ['sell_price', '平仓价格'],
                ['sell_number', '平仓数量'],
                ['repay_creat_money', '返还信用金'],
                ['repay_profits', '获得收益'],
                ['creat_time', '建仓时间','datetime'],
                ['sell_time', '平仓时间','datetime'],
               
            ])
             ->setColumnWidth('order_no', 150)
            ->setColumnWidth('vip_phone,vip_name,creat_time,sell_time', 140)
           // ->addRightButton('custom',$btn_access) // 批量添加右侧按钮
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
  }

  public function old_export(){
    // 查询
        $map = $this->getMap();

        $map['o.status'] = array("in",'4,5');
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = TradeModel::getbuylist($map,$order);
        foreach ($data_list as $key => $value) {
         
            $data_list[$key]['creat_time'] = date("Y-m-d H:i:s",$value['creat_time']);
            $data_list[$key]['sell_time'] = date("Y-m-d H:i:s",$value['sell_time']);
            $data_list[$key]['buy_type'] = $value['buy_type'] == '1'?'实盘':'模拟';
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['order_no','auto',  '订单号'],
            ['buy_type','auto',  '实盘状态'],
            ['vip_phone','auto',  '手机号'],
            ['vip_name','auto',  '用户名'],
            ['gupiao_name','auto',  '股票名称'],
            ['gupiao_code','auto',  '股票代码'],
            ['trush_price','auto',  '委托价'],
            ['trush_number','auto',  '数量'],
            ['now_price','auto',  '现价'],
            ['stop_win','auto',  '止盈价'],
            ['stop_down','auto',  '止损价'],
            ['credit_money','auto',  '信用金'],
            ['credit_rate','auto',  '信用倍率'],
            ['sell_type','auto',  '委托类型'],
            ['sell_price','auto',  '平仓价格'],
            ['sell_number','auto',  '平仓数量'],
            ['repay_creat_money','auto',  '返还信用金'],
            ['repay_profits','auto',  '获得收益'],
            ['creat_time','auto',  '建仓时间'],
            ['sell_time','auto',  '平仓时间'],

        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['历史策略列表', $cellName, $data_list]);
    }
//获取5档行情
  private function getwudang($code){
      $parms = '{"req":"Trade_QueryQuote","rid":"10","para":{"Codes" : "'.$code.'","JsonType" : 1,"Server" : 1}}';
      $res = get_socket_info($parms);
      $list = json_decode($res,true);
      return $list['data']['1'];
    }

     public function reload(){
      //echo exec('whoami');
       exec('/www/wwwroot/iclassical.net.cn/checksocket.sh 2>&1',$log,$status);
       // print_r($log);die;
        if($status==0){
              $this->success('重启成功'); 
            }else{
              $this->success('重启失败'); 
            }
  }
  public function trading_start(){
    $check = '{"req":"Trade_CheckStatus","rid":"10","para":{"Server" : 2}}';
    $checkres = get_socket_info($check);
    //print_r($checkres);exit;
    if(empty($checkres) || $checkres == false){
           $this->error('交易服务通讯失败，请联系管理员处理');
      }
    $checkres_d = json_decode($checkres,true);
    if(isset($checkres_d['data']['Status'])){
        if($checkres_d['data']['Status'] == '0'){
          $this->success("交易连接正常，无需重启");
        }
    }
    $parm = '{"req":"Server_Login","rid":"1001","para":{"LoginID" : "rongsheng","LoginPW" : "admin001","Encode" : 0}}';
    $res = get_socket_info($parm);
    sleep(5);
     $this->success("交易连接请求已发送");
  }

}