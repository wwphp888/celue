<?php
namespace app\api\controller;

use app\api\controller\Base;

use think\Db;
class index extends Base{

	public function index(){


		if($this->param['type']){

			
			$condition = $this->param['type'];

			switch ($condition) {

				case 'trade':
				
				 $list = Db::name('trade_order')
			        ->field('a.*,v.vip_phone,CONVERT(a.repay_profits/(a.trush_price*a.trush_number),decimal(18,2)) syl')
			        ->alias('a')
			        ->join('vip v','v.id=a.user_id')
			        ->where('a.status != 1') //计算已完成订单
			        ->limit(10)
			        ->order('syl desc')
			        ->select(); 
			        foreach ($list as $key => $value) {
			        $list[$key]['vip_phone'] = substr_replace($value['vip_phone'], '******', 3);
			        $list[$key]['deal_time'] = date('Y-m-d',$value['deal_time']);
			        $list[$key]['head_img'] = getheadimg($value['user_id']);
			        $list[$key]['sell_times'] = second2string($value['sell_time'],1,1);
			        //$list[$key]['syl'] = round($value['repay_profits']/($value['trush_price']*$value['trush_number'])*100,2);
			        } 
				break;
				case 'gupiao':

				/*$code_list=array_count_values(array_column($list,'gupiao_code'));
				arsort($code_list);
				foreach ($code_list as $key => $value) {
					
				}*/
				$list=Db::name('gupiao_list')
				->field('g.*,count(t.gupiao_code) count')
				->alias('g')
				->join('trade_order t','t.gupiao_code = g.code')
				->where('g.status = 1')
				->limit(10)
				->group('t.gupiao_code')
				->order('count desc')
				->select();
			
					break;
				case 'vip':
				$list=Db::name('vip')
				->field('g.vip_name,g.id,g.vip_phone,CONVERT(t.repay_profits/(t.trush_price*t.trush_number),decimal(18,2)) syl,sum(t.repay_profits/(t.trush_price*t.trush_number)) sum,count(t.id) counts')
				->alias('g')
				->join('match_order t','t.user_id = g.id')
				->where('g.status = 1')
				->limit(10)
				->group('t.user_id')
				->order('sum desc')
				->select();
					break;
				
				default:
					$list = [];
					break;
			}



			

		}
		$thumb = Db::name('cms_slider')->where('status = 1')->order('sort asc')->select();
		foreach ($thumb as $key => $value) {
			$thumb[$key]['img'] = get_thumb($value['cover']);
		}
		
		$data = [
			'banner'=>$thumb,
			'trade'=>$list
		];

		return msgreturn($data,'');


	}

	public function trade(){

		$shangzheng  = zhishu_d("s_sh000001"); //上证指数s_sh000001
        $shenzheng   = zhishu_d("s_sz399001"); //深证指数s_sz399001
        $hushen300   = zhishu_d("s_sh000300"); //沪深300 s_sh000300
        $chuangyeban = zhishu_d("s_sz399006"); //创业板指

         foreach ($shangzheng as $key => $value) {
            # code...
            $shangzheng2['name'] = substr($shangzheng[0],strpos($shangzheng[0],'"')+1);
            $shangzheng2['price'] = number_format($shangzheng[1],2,'.','');
            $shangzheng2['left']  = number_format($shangzheng[2],2,'.','');
            $shangzheng2['right'] = number_format($shangzheng[3],2,'.','');       //$shangzheng['name'] = $shenzheng[0]

        }

        foreach ($shenzheng as $key => $value) {
            # code...
            $shenzheng2['name'] = substr($shenzheng[0],strpos($shenzheng[0],'"')+1);
            $shenzheng2['price'] = number_format($shenzheng[1],2,'.','');
            $shenzheng2['left']  = number_format($shenzheng[2],2,'.','');
            $shenzheng2['right'] = number_format($shenzheng[3],2,'.','');      //$shangzheng['name'] = $shenzheng[0]

        }  
       foreach ($chuangyeban as $key => $value) {
            # code...
            $chuangyeban2['name'] = substr($chuangyeban[0],strpos($chuangyeban[0],'"')+1);
            $chuangyeban2['price'] = number_format($chuangyeban[1],2,'.','');
            $chuangyeban2['left']  = number_format($chuangyeban[2],2,'.','');
            $chuangyeban2['right'] = number_format($chuangyeban[3],2,'.','');            //$shangzheng['name'] = $shenzheng[0]

        } 
		$trade_down = Db::name('match_order')
			->field('id,gupiao_name,gupiao_code,now_price,yest_price,CONVERT((now_price-yest_price)/yest_price*100,decimal(18,2)) zf,CONVERT(now_price-yest_price,decimal(18,2)) zd')
			->where('status !=1 && (now_price-yest_price) < 0')
			->limit(5)
			->group('gupiao_code')
			->order('zf asc')
			->select();
		$trade_up = Db::name('match_order')
			->field('id,gupiao_name,gupiao_code,now_price,yest_price,CONVERT((now_price-yest_price)/yest_price*100,decimal(18,2)) zf,CONVERT(now_price-yest_price,decimal(18,2)) zd')
			->where('status !=1 && (now_price-yest_price) > 0')
			->limit(5)
			->group('gupiao_code')
			->order('zf desc')
			->select();
		/*$trade_asc = $list->order('zf asc')->select();	

		$trade_desc = $list->order('zf desc')->select();*/	
	
		$data = [
			'top'=>[
				'sh'=>$shangzheng2,
				'sz'=>$shenzheng2,
				'cb'=>$chuangyeban2
			],
			'trade_down'=>$trade_down,
			'trade_up'  =>$trade_up

		];
		
		return msgreturn($data,'');
	}

	public function invest(){


		$code = $this->param['code']?:'600000';

		$info =Db::name('gupiao_list')
		->field('id,code,title')
		->where('code','=',$code)
		->where('status','=',1)
		->find();

		$hqinfo = get_code_info($info['code']);
      	if($hqinfo[1][3]){
	    $hqinfo[1][12] = intval($hqinfo[1][12]/10000);
		//print_r($hqinfo);exit;
		$info['hq_info'] = $hqinfo[1];
		
		$info['zf'] = round(($hqinfo[1][3]-$hqinfo[1][4])/$hqinfo[1][4]*100,2);
		$info['zd'] = round($hqinfo[1][3]-$hqinfo[1][4],2);
		//$info['recomond_money'] = [1000,3000,5000,8000,10000];
        $info['recommond_money'] = explode("|",module_config('trade.strategy_credit_rec'));
        $info['rate']=explode("|",module_config('trade.strategy_rate'));
		$info['money'] = ceil($hqinfo[1][3]/$info['rate'][0])*100;
		$info['up_price'] = round($hqinfo[1][3]*(module_config('trade.winstop')/100+1),2);
		$info['up_rate']  = module_config('trade.winstop');
		$info['number']   = intval($info['money']*$info['rate'][0]/$hqinfo[1][3]/100)*100;
		$info['down_price'] = number_format((($hqinfo[1][3]*$info['number'])-($info['money']*module_config('trade.downstop')/100/$info['number'])),2,'.','');
		//$info['down_rate'] =number_format(($hqinfo[1][3]-$info['down_price'])/$hqinfo[1][3]*100,2,'.','');
        $info['down_rate'] =module_config('trade.downstop');
		//$info['rate'] =[2,5,10,15];strategy_rate
        
        //  print_r($info['rate'][0]);exit;
         // print_r($info['rate']);exit;
		foreach ($info['rate'] as $key => $value) {
			$rate_list[$key]['rate'] = $value;
			//$rate_list[$key]['rect_money'] =round(intval(ceil($hqinfo[1][3]/2)*100*$value/$hqinfo[1][3]/100)*100*$hqinfo[1][3],2);
          	$rate_list[$key]['rect_money']=round(((($info['money']*$value)+$info['money'])/$hqinfo[1][3]/100)*100*$hqinfo[1][3],2);
		}
		$info['strategy_renewal_fee']=module_config('trade.strategy_renewal_fee');
		$info['strategy_fee'] = module_config('trade.strategy_fee');
		$info['rate_list'] = $rate_list;
		

		return msgreturn($info,'');
        }else{
          // $info['recommond_money'] = explode("|",module_config('trade.strategy_credit_rec'));
          // $info['rate']=explode("|",module_config('trade.strategy_rate'));
         // return msgreturn($info,'');
        return msgreturn('','股票暂无交易数据');
        }

	}
  	public function get_code_info(){
    
    	$code = $this->param['code']?:'600000';

		$info =Db::name('gupiao_list')
		->field('id,code,title')
		->where('code','=',$code)
		->where('status','=',1)
		->find();

		$hqinfo = get_code_info($info['code']);
      //echo "<pre>";
     // var_dump($hqinfo);
      	if(isset($hqinfo[1][3])){
	    $hqinfo[1][12] = intval($hqinfo[1][12]/10000);
		//print_r($hqinfo);exit;
		$info['hq_info'] = $hqinfo[1];
        $info['zf'] = round(($hqinfo[1][3]-$hqinfo[1][4])/$hqinfo[1][4]*100,2);
		$info['zd'] = round($hqinfo[1][3]-$hqinfo[1][4],2);
    
        return msgreturn($info,'');
        }else{
        return msgreturn('','股票暂无交易数据');
        }
    
    
    
    }
	
	public function invest_search(){ 

		if($this->param['code']){

		$map['title|code|pinyin'] = array('like', '%'.$this->param['code'].'%');
			$list =Db::name('gupiao_list')
			->field('title,code,pinyin')
			->where($map)
             ->where("status",1)
			->limit(10)
			//->fetchsql(true)
			->select();

			return msgreturn($list,'');
		}
    }
	public function about(){



		$info = Db::name("cms_page")->where("id = 1")->find();



		return msgreturn($info,'');

	}
	


/////行情资讯 ///
	//公告
	public function newgglist(){
		  $gglist = Db::name("cms_document")->where("cid = 9")->paginate(10)->toArray()['data'];
		   foreach ($gglist as $key => $value) {
		  	$gglist[$key]['create_times'] = date("Y-m-d",$value['create_time']);
		  }

		  return msgreturn($gglist,'');
	}
	//行情
	public function newhqlist(){
		  $hqlist = Db::name("cms_document")->where("cid = 10")->paginate(10)->toArray()['data'];
		  foreach ($hqlist as $key => $value) {
		  	$hqlist[$key]['create_time'] = date("Y-m-d",$value['create_time']);
		  }
		
		  return msgreturn($hqlist,'');
	}

/////行情详情/////
	 public function newdetail(){
    	$id = request()->param('id');
       $detail = Db::view('cms_document d',true)
       				->view('cms_document_listinfos i','content','d.id = i.aid')
       				->where("d.id = {$id}")
       				->find();
       	$detail['create_time'] = date("Y-m-d",$detail['create_time']);
       	return msgreturn($detail,'');
    }

	public function getadvert(){
    
    	if(input('param.type')){
        
        
        
        
        	        
        	$id = input('param.type');
        
        	$result =  Db::name('cms_advert')->where("id={$id} and status=1")->field('content,name')->find();
        if($bg){
           $match_str = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/"; 
            preg_match_all ($match_str,$result['content'],$out,PREG_PATTERN_ORDER);
            $result['content']=$out[1][0];
        }
       	return msgreturn($result,'');

        
        }
    	
    
    
    
    }
//////////////下面是一些行情接口方法////////////////
      /*
     * market_bat 批量实时行情接口函数
     * 可通过get或post方式访问返回json格式数据
     * @code 必传参数 多个股票代码，代码间用逗号隔开 如果查询上证指数需要用sh000001
     */
    public function market_bat()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['data' => null, 'status' => 0, 'message' => '缺少参数code，操作失败']);
        }
        $data = z_market_bat($code);
      //print_r($data);exit;
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
     // echo '<pre>';print_r($data);exit;
        foreach($data as $k=>$v){
            //$now = z_market($v['code']);
            $p_range = floatval($v['current_price']) - floatval($v['yesterday_price']);
            $data[$k]['price_range'] = round($p_range,2);
            $data[$k]['price_rate'] = round(($p_range/floatval($v['yesterday_price'])*100),2);
        }
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);
    }

    /*
 * 行业涨幅板块
 */
    public function sinahy()
    {
        $data =sinahy();
      	$data=array_slice($data,0,6);
      //echo '<pre>';print_r($data);exit;
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);
    }
    /*
     * 涨幅前个股
     */
    public function stock_top10()
    {
        $data = z_stock_top10();
        if (empty($data)) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);
    }
    /*
     * 跌幅前个股
     */
    public function stock_bot10()
    {
        $data = z_stock_bot10();
        if (empty($data)) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);
    }

    /*
     * market 实时行情接口函数
     * 可通过get或post方式访问返回json格式数据
     * @code 必传参数 股票代码 如果查询上证指数需要用sh000001
     */
    public function market()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['data' => null, 'status' => 0, 'message' => '缺少参数code，操作失败']);
        }
        $data = z_market($code);
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        $p_range = $data['current_price'] - $data['yesterday_price'];
        $data['price_range'] = round($p_range,2);
        $data['price_rate'] = round(($p_range/$data['yesterday_price']*100),2);
        
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);

    }
      /*
     * 股票日K线数据
     * @code 必传参数 股票代码 如果查询上证指数需要用sh000001
     */
    public function day_k()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['status' => 0, 'message' => '缺少参数code，查询失败']);
        }
        $data = z_day_k($code);
         
      	foreach($data as $k=>$v){
        
        	$new_data[] =[intval($v['time']),floatval($v['open']),floatval($v['high']),floatval($v['low']),floatval($v['close']),intval($v['volume'])];
        
        
        }
   
 
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        //均价线
        $now = z_market($code);
        foreach($data as $k=>$v){
            $data[$k]['price_equal'] = round(($now['turnover']/$now['volume']*100),2);
        }
        return json(['data' => $new_data, 'status' => 1, 'message' => '操作成功']);

    }

    /*
     * 股票周K线数据
     * @code 必传参数 股票代码 如果查询上证指数需要用sh000001
     */
    public function week_k()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['status' => 0, 'message' => '缺少参数code，查询失败']);
        }
        $data = z_week_k($code);
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        
      	foreach($data as $k=>$v){
        
        	$new_data[] =[intval($v['time']),floatval($v['open']),floatval($v['high']),floatval($v['low']),floatval($v['close']),intval($v['volume'])];
        
        
        }
        //均价线
        $now = z_market($code);
        foreach($data as $k=>$v){
            $data[$k]['price_equal'] = round(($now['turnover']/$now['volume']*100),2);
        }
        return json(['data' => $new_data, 'status' => 1, 'message' => '操作成功']);

    }

    /*
     * 股票月K线数据
     * @code 必传参数 股票代码 如果查询上证指数需要用sh000001
     */
    public function month_k()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['status' => 0, 'message' => '缺少参数code，查询失败']);
        }
        $data = z_month_k($code);
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        foreach($data as $k=>$v){
        
        	$new_data[] =[intval($v['time']),floatval($v['open']),floatval($v['high']),floatval($v['low']),floatval($v['close']),intval($v['volume'])];
        
        
        }
        //均价线
        $now = z_market($code);
        foreach($data as $k=>$v){
            $data[$k]['price_equal'] = round(($now['turnover']/$now['volume']*100),2);
        }
        return json(['data' => $new_data, 'status' => 1, 'message' => '操作成功']);

    }

    /*
     * 股票分时K线数据
     * @code 必传参数 股票代码 如果查询上证指数需要用sh000001
     */
    public function minute_k()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['status' => 0, 'message' => '缺少参数code，查询失败']);
        }
        $data = z_minute_k($code);
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
        //均价线
        $now = z_market($code);
        foreach($data as $k=>$v){
            $data[$k]['price_equal'] = round(($now['turnover']/$now['volume']*100),2);
          	$data[$k]['volume'] = intval($v['volume']);
          	$data[$k]['time'] = intval($v['time']);
          $data[$k]['price'] = floatval($v['price']);
        }
        
        return json(['data' => $data, 'status' => 1, 'message' => '操作成功']);
    }
   public function market_only()
    {
        $req = request();
        $code = $req::instance()->param('code');
        if ($code === null) {
            return json(['data' => null, 'status' => 0, 'message' => '缺少参数code，操作失败']);
        }
        $data = z_market($code);
        if (!$data) {
            return json(['data' => $data, 'status' => 0, 'message' => '操作失败']);
        }
		$code_data = [
        	'code'=>$data['code'],
          'name'=>$data['name'],
          'yesterday_price'=>$data['yesterday_price'],
          'open_price'=>$data['open_price'],
          'current_price'=>$data['current_price'],
          'highest'=>$data['highest'],
          'lowest'=>$data['lowest'],
          'time'=>$data['time'],
          'volume'=>$data['volume']
        
        
        ];
        
        return json(['data' => $code_data, 'status' => 1, 'message' => '操作成功']);

    }








}