var data = $.parseJSON($("#daychart").text());
	//console.log(data)
	function getKLData() {
    var result = {};
    var ks = [];
    for (var i = 0; i < data.length; i++) {
        var rawData = data[i];
        var item = {
            quoteTime: rawData[0],
            preClose: 0,
            open: rawData[1],
            high: rawData[2],
            low: rawData[3],
            close: rawData[4],
            volume: rawData[5],
           amount: 0
        };
        if (ks.length == 0) {
            result.low = item.low;
            result.high = item.high;
        } else {
            result.high = Math.max(result.high, item.high);
            result.low = Math.min(result.low, item.low);
        }
        ks.push(item);
    }
    result.ks = ks;
    return result;
}