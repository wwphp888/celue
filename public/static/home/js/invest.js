
	var stname = '平安银行';
	var stcode='000001';//默认平安银行
	var klines = '<iframe name="Kcharts" src="http://stockpage.10jqka.com.cn/HQ_v4.html#hs_'+stcode+'" width="619" height="550" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>';
	var priceData;
	var min_money = 1000; //最小名义本金
	var number = 100; //买入数量
	var strategy_rate; //初始倍率
	var strategy_rate_list; //倍率列表
	var winstop;
	var downstop;
	var strategy_fee; //综合服务费
	var changes_name = false;
    var postdata = new Array();
	//搜索===================
	$(document).ready(function () {
		 tradeinit();
	    $("#search_input").val(stname+"   "+stcode); //此处空格至少两个
	    $(".klines").html(klines);
	    //获取持仓列表
	    gettradeorderlist();
	   

	});

//点击清空搜索框
$("#search_input").bind("focus",function(){
	$("#search_input").val('');
})
//失去焦点设置搜索框
$("#search_input").bind("blur",function(){
	var key = $("#search_input").val();
	if(key == ''){
		 $("#search_input").val(stname+"   "+stcode); //此处空格至少两个
	}
})
//监听搜索框
	$("#search_input").bind("input",function(){
		 var key = $("#search_input").val();
	    if(key){
	        $(".searchDowndiv").show();
	        searchStock(key);
	    }else{
	        $(".searchDowndiv").hide();
	    }
		
	})

//初始化部分参数
function tradeinit(){
	$.get("./invest/tradeinit",function(res){
		winstop = res['winstop']; //初始止盈
		downstop = res['downstop'];//初始止损
		strategy_rate = res['strategy_rate'];//初始倍率
		strategy_rate_list = res['strategy_rate_list'];//初始倍率列表
		strategy_fee = res['strategy_fee']; //初始化综合服务费
		 $("#SearchBtn").click();
      
	})
}

//设置搜索参数
function selectSetcode(code,name){
	if(stcode != code){
		changes_name = true;
	}
	stcode = code;
	stname = name;
	$("#money").val('');
	var klines = '<iframe name="Kcharts" src="http://stockpage.10jqka.com.cn/HQ_v4.html#hs_'+stcode+'" width="619" height="550" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>';
	$("#search_input").val(stname+"   "+stcode);//此处空格至少两个
	$(".searchDowndiv").hide();
    $("#SearchBtn").click();

    $(".klines").html(klines);
}

//请求数据
$("#SearchBtn").bind("click",function(){
	getgupiaoinfos();
})

function getgupiaoinfos(){
	$.post("./invest/getsocketinfo",{"stcode":stcode},function(res){
		priceData = res;
		var titybs;
		//基本行情
		if(res[3] == 0){
			titybs = '<p class="sttyname">'+stname+'<span>'+res[1]+'</span></p><p class="sttymoney black">'+res[4]+'</p>';
			titybs+='<p class="float">0.0 &nbsp;&nbsp;&nbsp;  0.00%</p>';
		}else{
			var zfnum = ((res[3]-res[4])/res[4]*100).toFixed(2); //振幅
        	var zdnum = ((res[3]-res[4])).toFixed(2); //涨跌
			if(res[3] > res[5]){
				titybs = '<p class="sttyname">'+stname+'<span>'+res[1]+'</span></p><p class="sttymoney">'+res[3]+'</p>';
				titybs+='<img class="up" src="/static/home/img/up.png" />';
				titybs+='<p class="float">'+zfnum+' &nbsp;&nbsp;&nbsp;  '+zdnum+'%</p>';
			}else{
				titybs = '<p class="sttyname">'+stname+'<span>'+res[1]+'</span></p><p class="sttymoney green">'+res[3]+'</p>';
				titybs+='<img class="up" src="/static/home/img/down.png" />';
				titybs+='<p class="float green">'+zfnum+' &nbsp;&nbsp;&nbsp;  '+zdnum+'%</p>';
			}
		}
		if(res['rss'] > 0){
			titybs+='<a href="javascript:void(0)" onclick="add_gprss(this)">+删自选</a>';
		}else{
			titybs+='<a href="javascript:void(0)" onclick="add_gprss(this)">+加自选</a>';
		}
		
		$("#titybs").html(titybs);
		//详细行情
		var zhangting = (res[4]*1.1).toFixed(2);
		var dieting = (res[4]*0.9).toFixed(2);
		var chengjiaoe = (res[12]/10000).toFixed(0);
		var canshuybs = '<ul><li>今开  <span>'+res[5]+'</span></li>';
		canshuybs += '<li>最高   <span>'+res[6]+'</span></li>';
		canshuybs += '<li>涨停价   <span class="red">'+zhangting+'</span></li>';
		canshuybs += '<li>成交量   <span>'+res[10]+'手</span></li>';
		canshuybs += '<li>昨收   <span>'+res[4]+'</span></li>';
		canshuybs += '<li>最低   <span>'+res[7]+'</span></li>';
		canshuybs += '<li>跌停价   <span class="green">'+dieting+'</span></li>';
		canshuybs += '<li>成交额   <span>'+chengjiaoe+'万</span></li></ul>';
		$("#canshuybs").html(canshuybs);

		//买卖档
		var wudangybs = '<ul><li>买盘档</li><li>卖盘档</li></ul><div class="sttybsl"><ul>';

			wudangybs +='<li><span class="left">买1</span><p ';
			wudangybs += res[17]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[17]+'</p><span class="right">'+res[19]+'</span></li>';

			wudangybs +='<li><span class="left">买2</span><p ';
			wudangybs += res[21]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[21]+'</p><span class="right">'+res[23]+'</span></li>';

			wudangybs +='<li><span class="left">买3</span><p ';
			wudangybs += res[25]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[25]+'</p><span class="right">'+res[27]+'</span></li>';

			wudangybs +='<li><span class="left">买4</span><p ';
			wudangybs += res[29]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[29]+'</p><span class="right">'+res[31]+'</span></li>';

			wudangybs +='<li><span class="left">买5</span><p ';
			wudangybs += res[33]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[33]+'</p><span class="right">'+res[35]+'</span></li>';

			wudangybs +='</ul></div><div class="sttybsl"><ul>';

			wudangybs +='<li><span class="left">卖1</span><p ';
			wudangybs += res[18]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[18]+'</p><span class="right">'+res[20]+'</span></li>';

			wudangybs +='<li><span class="left">卖2</span><p ';
			wudangybs += res[22]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[22]+'</p><span class="right">'+res[24]+'</span></li>';

			wudangybs +='<li><span class="left">卖3</span><p ';
			wudangybs += res[26]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[26]+'</p><span class="right">'+res[28]+'</span></li>';

			wudangybs +='<li><span class="left">卖4</span><p ';
			wudangybs += res[30]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[30]+'</p><span class="right">'+res[32]+'</span></li>';

			wudangybs +='<li><span class="left">卖5</span><p ';
			wudangybs += res[34]>res[4]?'class="red"':'class="green"';
			wudangybs +='>'+res[34]+'</p><span class="right">'+res[36]+'</span></li>';
			
			wudangybs +='</ul></div>';	
		  $("#wudangybs").html(wudangybs);
		
		var money = $("#money").val();
		if(money < 1){
			min_money = Math.ceil(priceData[3]*100/parseInt(strategy_rate)/100)*100;
			money = min_money;
			benjin_tjs(min_money);
			$("#money").val(min_money);
			var ying_moneys = parseInt(money)+parseFloat(parseInt(money)/10000*parseFloat(strategy_fee));
			$("#ying_money").html(ying_moneys);
			stra_rates(money,strategy_rate);

		}
		var crd1 = $("#crd1").val();
		var crd2 = $("#crd2").val();
		if(changes_name || crd1 == 0 || crd2 == 0){
			//console.log(winstop);
			var winstop_price  = (parseFloat(priceData[3]*winstop/100)+parseFloat(priceData[3])).toFixed(2);
			$("#crd1").val(winstop_price);
			$("#win_rate").html(winstop);
			//止损价格
			var downstop_price = parseFloat(((priceData[3]*number)-(money*downstop/100))/number).toFixed(2);
			//止损比例
			var down_rate = parseFloat((priceData[3]-downstop_price)/priceData[3]*100).toFixed(2);
			$("#crd2").val(downstop_price);
		    $("#down_rate").html(down_rate);
		    changes_name = false;
		}
				

	})
}setInterval("getgupiaoinfos()",3000);

//加入自选
function add_gprss(obj){
	
	$.get("./invest/add_gprss",{"gupiao_name":stname,"gupiao_code":stcode},function(res){
			layer.msg(res.message);
		
	})
}

//检索股票列表
function searchStock(key){
	$.get("./invest/searchStock",{"str":key},function(res){
		var strlist = '<ul><li class="hd"><em class="searc_name">名称</em><em class="searc_code">代码</em><em class="searc_jian">简拼</em></li></ul>';
			for(var i=0;i<res.length;i++){
			 	strlist = strlist+'<ul><li onclick="selectSetcode(\''+res[i].code+'\',\''+res[i].title+'\')"><em class="searc_name">'+res[i].title+'</em><em class="searc_code">'+res[i].code+'</em><em class="searc_jian">'+res[i].pinyin+'</em></li></ul>';
			}
			$("#sreachUl").html(strlist);
	})
}

//参考本金 
function benjin_tjs(money){

	var benjin_tj = '';
	//总金额
	var all_m = parseInt(money*strategy_rate);

	number = parseInt(((parseInt(all_m))/priceData[3])/100)*100;
	$("#allnumber").html(number);
	//综合服务费
	var yingstrategy_fee = parseFloat(parseInt(all_m)/10000*strategy_fee).toFixed(2);
	//var yingstrategy_fee = parseFloat((parseInt(money)*parseInt(strategy_rate)+parseInt(money))*parseInt(strategy_fee[0])/10000+parseInt(money)*parseInt(strategy_rate)*parseInt(strategy_fee[1])/10000).toFixed(2);
	$("#strategy_fee").html(yingstrategy_fee);
	var ying_moneys = parseFloat(parseInt(money)+parseFloat(yingstrategy_fee)).toFixed(2);
	$("#ying_money").html(ying_moneys);
	for (var i = 0; i < strategy_rate_list.length; i++) {
		//strategy_rate = strategy_rate_list[0]; //赋值信用金倍率
		var quzheng = parseInt(parseInt(parseInt(money*strategy_rate_list[i]))/priceData[3]/100);
		var yings = parseInt(quzheng*priceData[3]*100);
		benjin_tj += '<li>'+yings+'元</li>';
	}
	$("#benjin_tj").html(benjin_tj);
	stra_rates(money,strategy_rate);	
	

}


//信用金选择
$('.stymoney li').click(function(){
		$('.stymoney li').eq($(this).index()).addClass('special').siblings().removeClass('special');
		var min_money = Math.ceil(priceData[3]/2)*100;
		if($(this).text() < min_money){
			$("#money").val(min_money);
			benjin_tjs(min_money);
			layer.msg("最少信用金为"+min_money);
		}else{
			$("#money").val($(this).text());
			benjin_tjs($(this).text());
		}
		
})
//手动输入信用金监听
$("#money").bind('blur',function(){
	var money = $("#money").val();
  
	min_money = Math.ceil(priceData[3]/strategy_rate)*100;
    benjin_tjs(money);
  //var yingstrategy_fee = parseFloat((parseInt(money)*parseInt(strategy_rate)+parseInt(money))*parseInt(strategy_fee[0])/10000+parseInt(money)*parseInt(strategy_rate)*parseInt(strategy_fee[1])/10000).toFixed(2);
	if(money < min_money){
		$("#money").val(min_money);
		 benjin_tjs(min_money);
		layer.msg("最少信用金为"+min_money);
	}
})
//信用金倍率选择
$('.stymoney1 li').click(function(){

		$('.stymoney1 li').eq($(this).index()).addClass('special').siblings().removeClass('special');
		var rate = $(this).text();
		var money = $("#money").val();
		strategy_rate = rate.substring(0,rate.length-1);
		stra_rates(money,strategy_rate);
		//综合服务费
		var yingstrategy_fee = parseFloat((parseInt(money)*parseInt(strategy_rate))/10000*strategy_fee).toFixed(2);
		//var yingstrategy_fee = parseFloat((parseInt(money)*parseInt(strategy_rate)+parseInt(money))*parseInt(strategy_fee[0])/10000+parseInt(money)*parseInt(strategy_rate)*parseInt(strategy_fee[1])/10000).toFixed(2);
		$("#strategy_fee").html(yingstrategy_fee);
		var ying_moneys = parseFloat(parseInt(money)+parseFloat(yingstrategy_fee)).toFixed(2);
		$("#ying_money").html(ying_moneys);
		
		//$("#money").val("￥"+$(this).text())
})

function stra_rates(money,strategy_rate){
	number = parseInt(parseInt(parseInt(money*strategy_rate))/priceData[3]/100)*100;
	$("#allnumber").html(number);
		//止盈价格
		//console.log(number);
		var winstop_price  = (parseFloat(priceData[3]*winstop/100)+parseFloat(priceData[3])).toFixed(2);
		//止损价格
		var downstop_price = parseFloat(((priceData[3]*number)-(money*downstop/100))/number).toFixed(2);
		//止损比例
		var down_rate = parseFloat((priceData[3]-downstop_price)/priceData[3]*100).toFixed(2);

		$("#crd1").val(winstop_price);
		$("#win_rate").html(winstop+"%");

		$("#crd2").val(downstop_price);
		$("#down_rate").html(down_rate+"%");

}

//止盈价格增加（无上限）
$(".add1").click(function(){
	var crd = $("#crd1").val()
	var add = (parseFloat(crd)+0.01).toFixed(2);
	$("#crd1").val(add);
	var winstop = parseFloat((add-priceData[3])/priceData[3]*100).toFixed(2);
	$("#win_rate").html(winstop+"%");
})
//止盈价格减少
$(".reduce1").click(function(){
	var crd = parseFloat($("#crd1").val())
	if(crd < parseFloat(priceData[3])){
		$("#crd1").val(priceData[3]);
		var winstop = parseFloat((priceData[3]-priceData[3])/priceData[3]*100).toFixed(2);
		$("#win_rate").html(winstop+"%");
	}else{
		var add = (parseFloat(crd)-0.01).toFixed(2);
		$("#crd1").val(add);
		var winstop = parseFloat((add-priceData[3])/priceData[3]*100).toFixed(2);
		$("#win_rate").html(winstop+"%");
	}

})
//手动输入止盈价监听
$("#crd1").bind('blur',function(){
	var crd = $("#crd1").val()
	var add = parseFloat(crd).toFixed(2);
	if(add < parseFloat(priceData[3])){
		console.log(priceData[3]);
		$("#crd1").val(parseFloat(priceData[3]));
		var winstop = parseFloat((priceData[3]-priceData[3])/priceData[3]*100).toFixed(2);
		$("#win_rate").html(winstop+"%");
		layer.msg("止盈价格不得低于当前价");
	}else{
		$("#crd1").val(add);
		var winstop = parseFloat((add-priceData[3])/priceData[3]*100).toFixed(2);
		$("#win_rate").html(winstop+"%");
	}
	
})

//止损价格增加
$(".add2").click(function(){
	var crd = $("#crd2").val()
	var add = (parseFloat(crd)+0.01).toFixed(2);
	if(add > priceData[3]){
		$("#crd2").val(parseFloat(priceData[3]));
		//止损比例
		var down_rate = parseFloat((priceData[3]-priceData[3])/priceData[3]*100).toFixed(2);
		$("#down_rate").html(down_rate+"%");
		layer.msg("止损价格不得高于当前价格");
	}else{
		$("#crd2").val(add);
		//止损比例
		var down_rate = parseFloat((priceData[3]-add)/priceData[3]*100).toFixed(2);
		$("#down_rate").html(down_rate+"%");
	}

	
})
$(".reduce2").click(function(){
	var crd = parseFloat($("#crd2").val())
	var money = $("#money").val();
	//止损价格
	var downstop_price = parseFloat(((priceData[3]*number)-(money*downstop/100))/number).toFixed(2);
	
	if(crd < downstop_price){
		var add = (parseFloat(crd)-0.01).toFixed(2);
		$("#crd2").val(downstop_price);
		//止损比例
		var down_rate = parseFloat((priceData[3]-downstop_price)/priceData[3]*100).toFixed(2);
		$("#down_rate").html(down_rate+"%");
		layer.msg("最小止损价格为"+downstop_price);
	}else{
		var add = (parseFloat(crd)-0.01).toFixed(2);
		$("#crd2").val(add);
		//止损比例
		var down_rate = parseFloat((priceData[3]-add)/priceData[3]*100).toFixed(2);
		$("#down_rate").html(down_rate+"%");
	}
})

$("#subuybotton").click(function(){
	//询问框

layer.confirm('您确定要提交并委托该策略吗？', {
			  btn: ['是的','取消'], //按钮
			  title:'提交策略'
			}, function(){

			  //自动续费
				var autoclass = $("#autofees").attr('class');
				if(autoclass == 'onoff on'){
					autostatus = 1;
				}else{
					autostatus = 0;
				}
				
				//同意协议
				var xieyi = $("input[name='xieyi']:checked").val();
				if(xieyi !== 'on'){
					layer.msg("请先阅读并同意协议条款");
					return false;
				}
				 //信用金
				 var money = $("#money").val();
				 if(money < min_money){
				 	layer.msg("信用金少于最低信用金");
				 	return false;
				 }
				 //信用倍率
				 if(strategy_rate < 1){
				 	layer.msg("请重新选择信用倍率");
				 	return false;
				 }
				 //止盈价格
				 var winstops = $("#crd1").val();
				 if(winstops == ''){
				 	layer.msg("止盈价格错误,请刷新页面");
				 	return false;
				 }
				 //止损价格
				 var downstops = $("#crd2").val();
				 if(downstops ==''){
				 	layer.msg("止损价格错误,请刷新页面");
				 	return false;
				 }
				
				 	 postdata['stname'] = stname;
				 	 postdata['stcode'] = stcode;
				 	 postdata['money'] = money;
				 	 postdata['strategy_rate'] = strategy_rate;
				 	 postdata['autostatus'] = autostatus;
				 	 postdata['winstops'] = winstops;
				 	 postdata['downstops'] = downstops;
				 	 postdata['number'] = number;
				 	 postdata['price'] = priceData[3];
				 	 console.log(postdata);
				 	 var autonames = autostatus == 1?'是':'否';
				 var htmls = '';
	    		     htmls += '<div class="oldinfoh1">您将要创建该策略，请核对策略信息</div>';
	                 htmls += '<div class="oldinfobody"><ul><li>'+stname+' <span>'+stcode+'</span></li></ul>';
					 htmls += '<ul><li>信用金 <span>'+money+'</span></li><li>信用金倍率<span>'+strategy_rate+'</span></li></ul>';
					 htmls += '<ul><li>当前价格 <span>'+priceData[3]+'</span></li><li>数量<span>'+number+'</span></li></ul>';
					 htmls += '<ul><li>止盈价 <span>'+winstops+'</span></li><li>止损价<span>'+downstops+'</span></li></ul>';
					 htmls += '<ul><li>是否自动续费 <span>'+autonames+'</span></li></ul></div>';
					 htmls += '<div class="oldbottons" onclick="submitall()">确定</div>';
					/* htmls += '<div class="oldinfobody"><ul><li>交易本金 <span>'+d.all_money+' </span></li><li>信用金<span>'+d.credit_money+'</span></li></ul>';
					 htmls += '<ul><li>交易综合服务费 <span>'+d.service_money+'</span></li><li>续期费用<span>'+d.defer_money+'</span></li></ul>';
					 htmls += '<ul><li>返还信用金 <span>'+d.repay_creat_money+'</span></li><li>交易盈亏<span>'+d.repay_profits+'</span></li></ul></div>';*/
					//$("#oldinfo").html(htmls);
		    		layer.open({
					  type: 1,
					  title:'策略详情',
					  area:['650px','470px'],
					  content: htmls
					});

				 /*$.post("./invest/tradebuy",{"stname":stname,"stcode":stcode,"money":money,"strategy_rate":strategy_rate,"autostatus":autostatus,"winstops":winstops,"downstops":downstops,'number':number,'price':priceData[3]},function(res){
				 	console.log(res);

				 	layer.msg(res.message,function(){
				 		window.location.reload();
				 	});

				 })*/



			}, function(){
				layer.closeAll();
			    return false;
			});
	

})

//提交
function submitall(){
console.log(postdata);

	$.post("./invest/tradebuy",{"stname":postdata.stname,"stcode":postdata.stcode,"money":postdata.money,"strategy_rate":postdata.strategy_rate,"autostatus":postdata.autostatus,"winstops":postdata.winstops,"downstops":postdata.downstops,'number':postdata.number,'price':postdata.price},function(res){
				 	console.log(res);

				 	layer.msg(res.message,function(){
				 		window.location.reload();
				 	});

				 })
}


//持仓订单
function gettradeorderlist(){
	$.get("./invest/gettradelist",function(res){
		var htmls='';
		for (var i =0; i < res.length; i++) {
			
			htmls += '<div class="positions"><div class="posmain"><dl>';
			htmls +='<dt>'+res[i].gupiao_name+'<span>'+res[i].gupiao_code+'</span></dt>';			
			htmls +='<dd>信用金<span>'+res[i].credit_money+'</span></dd></dl>';
			htmls +='<dl><dt>止盈价    <span class="red">'+res[i].stop_win+'</span></dt>';
			htmls +='<dd>止损价    <span class="green">'+res[i].stop_down+'</span></dd></dl>';
			htmls +='<dl><dt>买入价    <span>'+res[i].trush_price+'</span></dt>';
			htmls +='<dd>当前价    <span class="red">'+res[i].now_price+'</span></dd></dl>';
			htmls +='<dl><dt>股票数量    <span>'+res[i].trush_number+'</span></dt>';
			htmls +='<dd>持仓盈亏    <span class="red">'+res[i].yingkui+'</span></dd></dl>';
			htmls +='<dl><dt>买入时间    <span>'+res[i].create_times+'</span></dt>';
			htmls +='<dd>已持仓      <span class="red">'+res[i].has_day+'天</span></dd></dl>';
			htmls +='<a class="sell" href="javascript:void(0)" onclick="subsell('+res[i].id+')">卖出</a></div>';

			htmls +='<div class="profit">';
			if(res[i].yingkui == 0){
				htmls +='<img src="/static/home/img/pft2.png"><p class="pft1">现价上涨 <span>'+res[i].stop_win_price+'</span>元即将触发平仓</p>';
			}
			if(res[i].yingkui > 0){
				htmls +='<img src="/static/home/img/pft1.png"><p class="pft1">现价上涨 <span>'+res[i].stop_win_price+'</span>元即将触发平仓</p>';
			}
			if(res[i].yingkui < 0){
				htmls +='<img src="/static/home/img/pft3.png"><p class="pft1">现价下跌 <span>'+res[i].stop_win_price+'</span>元即将触发平仓</p>';
			}

			htmls +='<a href="javascript:void(0)" onclick="stopwinedit('+res[i].id+')">修改止盈价</a>';
			htmls +='<a href="javascript:void(0)" onclick="stopdownedit('+res[i].id+')">修改止损价</a>';
			htmls +='<div class="cell-right" style="margin-top:-5px;">';

			if(res[i].defer_status == 1){
				htmls +='<p class="onoff on" id="wifi'+res[i].id+'" onclick="setswitch('+res[i].id+',0)"><span></span></p>';
				//htmls +='<span class="switch-on" onclick="setswitch('+res[i].id+',0)" themeColor="#ec4229"></span>';
			}else{
				htmls +='<p class="onoff off" id="wifi'+res[i].id+'" onclick="setswitch('+res[i].id+',1)"><span></span></p>';
				//htmls +='<span class="switch-off" onclick="setswitch('+res[i].id+',1)"  themeColor="#ec4229"></span>';
			}
			htmls +='</div><p class="pft2">递延费<span>'+res[i].defer_money+'</span>元/万/天</p></div></div>';
			htmls +="<script>$('.onoff').on('click',function(){if($('.onoff').is('.off')){$('.onoff').removeClass('off');";
			htmls +="$('.onoff').addClass('on');}else{$('.onoff').removeClass('on');$('.onoff').addClass('off');}})</script>";
		
		//$.getScript("/static/home/js/honeySwitch.js");
		$("#trade_strategy_list").html(htmls);
        }
	})
}setInterval("gettradeorderlist()",60000);

//大赛持仓
//修改止盈价
var matchinfo;
function stopwinedit(id){
	$.get("./invest/gettradeorder",{"id":id},function(res){
		matchinfo = res;
		console.log(res);
		var htmls = '<div class="stymoney_tips" style="height:95%"><p>止盈价格</p>';
		htmls += '<div class="winadd1"><p class="reduce1" onclick="winadd1()">-</p>';
		htmls += '<input id="winedit" type="text" value="'+res.stop_win+'" />';
		htmls += '<p class="winadd2" onclick="winadd2()">+</p></div><div class="clear"></div>';
		htmls += '<a class="tips_a" href="javascript:void(0)" onclick="dostopwinedit('+id+')">确认修改</a></div>';
		layer.open({
		  type: 1,
		  title:'止盈价修改',
		  skin: 'layui-layer-rim', //加上边框
		  area: ['420px', '240px'], //宽高
		  content: htmls
		});
	})
	
}

function winadd1(){
	var winedit = parseFloat($("#winedit").val());
	//console.log(winedit);
	//console.log(matchinfo['now_price']);
	if(winedit <= parseFloat(matchinfo['now_price'])){
		$("#winedit").val(matchinfo['now_price']);
		layer.msg("止盈价格不得低于当前价");
	}else{
		var add = (parseFloat(winedit)-0.01).toFixed(2);
		$("#winedit").val(add);
	}
}
function winadd2(){
	var crd = $("#winedit").val();
	var add = (parseFloat(crd)+0.01).toFixed(2);
	$("#winedit").val(add);
}
//手动输入止盈价监听
$("#winedit").bind('blur',function(){
	var crd = $("#winedit").val();
	console.log(crd);
	var add = parseFloat(crd).toFixed(2);
	if(add < parseFloat(matchinfo['now_price'])){
		$("#winedit").val(parseFloat(matchinfo['now_price']));
		layer.msg("止盈价格不得低于当前价");
	}else{
		$("#winedit").val(add);
	}
	
})
var checknowstatus = true;
function dostopwinedit(id){
		if(checknowstatus == false){
			layer.msg("请勿重复提交");
			return false;
		}
		var crd = $("#winedit").val();
		//console.log(crd);
		var add = parseFloat(crd).toFixed(2);
		if(add <= parseFloat(matchinfo['now_price'])){
			$("#winedit").val(parseFloat(matchinfo['now_price']));
			layer.msg("止盈价格不得低于当前价");
			return false;
		}
		checknowstatus = false;
		$.get("./invest/setstopstatus",{"id":id,"type":"1","val":add},function(res){

				if(res.status == 1){
					layer.msg(res.message,function(){
						checknowstatus = true;
						layer.closeAll();
					});
				}else{
					checknowstatus = true;
					layer.msg(res.message);
				}
		})

}
//修改止损价
var mindownstop_price; //最低止损价格初始化
function stopdownedit(id){
		$.get("./invest/gettradeorder",{"id":id},function(res){
		matchinfo = res;
		//最低止损价格
		mindownstop_price = parseFloat(((res['trush_price']*res['trush_number'])-(res['credit_money']*downstop/100))/res['trush_number']).toFixed(2);
		console.log(mindownstop_price);
		var htmls = '<div class="stymoney_tips" style="height:95%"><p>止损价格</p>';
		htmls += '<div class="winadd1"><p class="reduce1" onclick="downadd1()">-</p>';
		htmls += '<input id="downedit" type="text" value="'+res.stop_down+'" />';
		htmls += '<p class="winadd2" onclick="downadd2()">+</p></div><div class="clear"></div>';
		htmls += '<p>信用金追加</p><div class="winadd1"><input class="cretmoneys" type="text" id="addcredit_money" value="" disabled="disabled" placeholder="暂时不用追加保证金" /></div>';
		htmls += '<div class="clear"></div><a class="tips_a" href="javascript:void(0)" onclick="dostopdownedit('+id+')">确认修改</a></div>';
		layer.open({
		  type: 1,
		  title:'止损价修改',
		  skin: 'layui-layer-rim', //加上边框
		  area: ['420px', '270px'], //宽高
		  content: htmls
		});
	})
}

//减少止损价
function downadd1(){
	var crd = parseFloat($("#downedit").val());
	if(crd <= 0){
		$("#downedit").val(mindownstop_price);
		layer.msg("止损价不得小于等于0");
		return false;
	}
	
	if(crd < mindownstop_price){
		$('#addcredit_money').removeAttr("disabled");
		$('#addcredit_money').val('0');
	}
	var add = (parseFloat(crd)-0.01).toFixed(2);
	$("#downedit").val(add);
	
}

//增加止损价
function downadd2(){
	var crd = parseFloat($("#downedit").val());
	if(crd >= matchinfo['now_price']){
		layer.msg("止损价不得大于等于当前价格");
		return false;
	}
	
	if(crd > mindownstop_price){
		$('#addcredit_money').attr("disabled","disabled");
		$('#addcredit_money').val('');
	}
	var add = (parseFloat(crd)+0.01).toFixed(2);
	$("#downedit").val(add);
}

//修改止损价确认提交
var checkdowntatus = true;
function dostopdownedit(id){
	if(checkdowntatus == false){
			layer.msg("请勿重复提交");
			return false;
	}
	var crd = parseFloat($("#downedit").val());
	var credit_money = parseInt($("#addcredit_money").val());

	if(isNaN(credit_money)){
		credit_money = 0;
	}
	if(crd <= 0){
		layer.msg("止损价格不得小于等于0");
		return false;
	}
	if(crd < mindownstop_price){
		//yingdownstop_price = parseFloat(((res['trush_price']*res['trush_number'])-(res['credit_money']*downstop/100))/res['trush_number']).toFixed(2);
		//应追加信用金额度
		yingdownstop_price = parseInt((matchinfo['trush_price']*matchinfo['trush_number']-crd)/downstop*100);
		console.log(yingdownstop_price);
		if(credit_money < yingdownstop_price){
			$('#addcredit_money').val(yingdownstop_price);
			layer.msg("如需修改为该止损价格，您最少应该追加信用金"+yingdownstop_price+'元');
			return false;
		}

	}
	if(crd >matchinfo['now_price']){
		layer.msg("止损价格不得大于等于当前价格");
		return false;
	}
	checkdowntatus = false;
	$.get("./invest/setstopstatus",{"id":id,"type":"2","val":crd,"credit":credit_money},function(res){

				if(res.status == 1){
					layer.msg(res.message,function(){
						checkdowntatus = true;
						layer.closeAll();
					});
				}else{
					checkdowntatus = true;
					layer.msg(res.message);
				}
		})

}

//修改递延费开启状态
function setswitch(id,status){
	layer.msg('设置中', {icon: 16,shade: 0.01,time:10000 });
	$.get("./invest/setswitch",{"id":id,"status":status},function(res){

		layer.closeAll();
		if(status == 1){
			$("#wifi"+id).removeClass("off").addClass("on");
			$("#wifi"+id).attr("onclick","setswitch("+id+",0)");
			layer.msg(res.message);
		}else{
			
			$("#wifi"+id).removeClass("on").addClass("off");
			$("#wifi"+id).attr("onclick","setswitch("+id+",1)");
			layer.msg(res.message);
		}
	})
}
//持仓卖出
function subsell(id){
	layer.confirm('您确定要提交委托卖出该持仓策略吗？', {
			  btn: ['是的','取消'], //按钮
			  title:'卖出策略'
			}, function(){
				layer.msg('正在卖出', {icon: 16,shade: 0.01,time:10000 });
				$.get("./invest/tosell",{"id":id},function(res){
					layer.closeAll();
					if(res.status){
						layer.msg(res.message);
					}else{
						
						
						layer.msg(res.message);
					}
				})
			}, function(){
				layer.closeAll();
			    return false;
			});
}


//同意条款
function celuemsg(){
	$.post('./invest/celuemsg',function(d){
				layer.open({
					  type: 1,
					  title: '平台策略协议',
					  skin: 'newlayer_content',
					  shadeClose: true,
					  shade: 0.5,
					  area: ['850px', '580px'],
					  content: d 
					}); 
			})
}
