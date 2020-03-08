
function setrss(id,obj){
	$.get("/index/ace/setRss",{"id":id},function(res){
		if(res){

			var name = $(obj).html();
			if(name =="订阅"){
				var newname = "已订阅";
			}else{
				var newname = "订阅";
			}
			$(obj).html(newname);
			layer.msg("操作成功");
		}else{
			layer.msg("操作失败");
		}
	})
}