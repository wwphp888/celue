	var mins = JSON.parse($('#mins').text());
	function getQuote() {
	
            return {
                quote: {
                    time: parseInt(mins[mins.length -1].time),
                    open: parseFloat(JSON.parse($('#data').text()).open_price),
                    preClose: parseFloat(JSON.parse($('#data').text()).yesterday_price),
                    highest: parseFloat(JSON.parse($('#data').text()).highest),
                    lowest: parseFloat(JSON.parse($('#data').text()).lowest),
                    price: parseFloat(JSON.parse($('#data').text()).current_price),
                    volume: parseFloat(JSON.parse($('#data').text()).volume),
                    amount: 38621178573
                  
                },mins
                
                
            };
        }
       
