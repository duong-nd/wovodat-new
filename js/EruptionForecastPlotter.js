function plotEruptionForecast(args) {
	var data = args.data;

	var ed_for_data = [];
	var end_of_time = 0;
	function convertDate(d) {
		var m = d.getMonth()+1;
		m = (m < 10)? '0' + m.toString() : m.toString();
		date = d.getDate();
		date = (date < 10)? '0' + date.toString() : date.toString();
		return (d.getFullYear() + '-' + m + '-' + date);
	}

	for (var i in data) {
		var ed_for = data[i];
		ed_for.ed_for_astime = parseInt(ed_for.ed_for_astime);
		ed_for.ed_for_aetime = parseInt(ed_for.ed_for_aetime);

		if(ed_for.ed_for_aetime == 0)
			ed_for.ed_for_aetime = new Date().getTime();
		var ed_for_astime = ed_for.ed_for_astime;
		var ed_for_aetime = ed_for.ed_for_aetime;
		end_of_time = Math.max(end_of_time, ed_for_aetime);
		ed_for_data.push([ed_for_astime, 2, 0, ed_for_aetime - ed_for_astime, ed_for]);
	}


	var param_ed_for = {
		label: "Alert level",
		data: ed_for_data,
		bars: {
			show: true,
			wovodat: true,
			drawBottom: true,
			lineWidth: 0
		},
		dataType: "ed_for"
	};

	var option = {
		grid: {
			hoverable: true
		},
		xaxis: {
			min: end_of_time - ONE_YEAR,
			max: end_of_time,
			autoscale: false,
			mode: "time",
			timeformat: "%Y-%m",
			tickSize: [1, "month"]
		},
		yaxis: {
			show:false,
			panRange: false
		}
	};
	if (window.eruption_plot != null) 
		$.extend(option.xaxis, {min: eruption_plot.getAxes().xaxis.options.min, max: eruption_plot.getAxes().xaxis.options.max});
	window.eruption_forecast_plot = $.plot($("#eruption_forecast_graph"), [param_ed_for], option);

	$("#eruption_forecast_graph").bind("plothover", function(event, pos, item) {
		if (item) {
            $("#tooltip").remove();
            var content;
    		
    		var ed_for = item.series.data[item.dataIndex][4];
    		content = ed_for.ed_for_alevel + "<br/>";
    		content += convertDate(new Date(ed_for.ed_for_astime));
    		content += " to ";
    		content += convertDate(new Date(ed_for.ed_for_aetime));

            showTooltip(pos.pageX, pos.pageY, content);
        } else {
            $("#tooltip").remove();
            previousItem = null;            
        }	
    });
}