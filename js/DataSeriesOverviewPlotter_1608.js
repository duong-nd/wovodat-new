function plotOverview(args) {
	var data = args.data;
	
	data_series = [];
	
	var max_data = -1000;
	var min_data = 1000;
	
	window.ds_selection_box = $("#data_series_checkbox");

	$.each(data, function(key, val) {
		console.log(key);
		console.log(val);
		$("#data_series_checkbox").append("<input type='checkbox' name='" + key +
			"' id='id" + key + "'></input>" +
			"<label for='id" + key + "'>"
			+ val['station']['code'] + "</label>	");
	});

	ds_selection_box.find("input").click(plotAccordingToChoices);
	param_dso = [];

	
	
	function initData() {
		for(var i in data) {
			var d = data[i];
			data_series[i] = [];
			for(var j in d.data_series) {
				ds = d.data_series[j];
				ds.component_data = parseInt(ds.component_data);
				if (ds.component_data > max_data) max_data = ds.component_data;
				if (ds.component_data < min_data) min_data = ds.component_data;
				data_series[i].push([ds.stime, ds.component_data, 0, ds.etime - ds.stime,
				ds]);
			}
		}
	}
	
	initData();
	var option_ds = {
		grid: {
			hoverable: true,
		},

		xaxis: {
			min: eruption_plot.getAxes().xaxis.options.min,
			max: eruption_plot.getAxes().xaxis.options.max,
			autoscale: true,
			mode: "time",
			timeformat: "%Y-%m",
			//tickSize: [1, "month"]
			//timeformat: "%Y-%m-%d %H:%M:%S"
		},
		yaxis: {
			min: min_data,
			max: max_data,
			autoscale: true,
	//		tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
		},

	};	
	function plotAccordingToChoices() {
		var data = [];
		var cnt = 0;
		param_dso = [];
		$("#data_series_overview").empty();
		for (i in ds_plot) {
			$(ds_plot[i]).empty();
		}
		
		ds_selection_box.find("input:checked").each(function () {
			var key = $(this).attr("name");
			
			if (key && data_series[key]) {
				param_dso.push({
					data: data_series[key],
					lines: {
						show: true,
						wovodat: true
					},
					dataType: "dso",
				});
			}
			cnt = cnt + 1;
		});
		if (param_dso.length > 0) 
			window.ds_overview_plot = $.plot($("#data_series_overview"), param_dso, option);
		if (param_dso) {
			console.log(param_dso[0].data);
		}
		for (i in param_dso) {
			param = [];
			param.push({
				data : param_dso[i].data,
				bars : {show: true, wovodat:true},
				dataType: "ds",
			});
			console.log(param);
			ds_plot_graph[i] = $.plot($(ds_plot[i]), param,option_ds);
		}
	}
	
/*	for (var i in data_series) {
		param_dso.push({
			//color: color_list[i],
			//label: "Overview",
			data: data_series[i],
			lines: {
				show: true,
				wovodat: true
			},
			dataType: "dso",
		});
	}
	*/
	var option = {
		grid: {
			hoverable: true,
		},
		xaxis: {
			min: eruption_plot.getAxes().xaxis.options.min,
			max: eruption_plot.getAxes().xaxis.options.max,
			autoscale: true,
			mode: "time",
			timeformat: "%Y-%m",
			tickSize: [1, "month"]
			
			//timeformat: "%Y-%m-%d %H:%M:%S"
		},
		yaxis: {
			min: min_data,
			max: max_data,
			autoscale: true,
			//tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
		},
		//selection : {mode : "x"	}
	};
	
	//window.ds_overview_plot = $.plot($("#data_series_overview"), param_dso, option);
	console.log(option);
	/*$("#data_series_overview").bind("plotselected", function (event, ranges) {
		for (var i in data_series){
			param_ds = {
				data: data_series[i],
				bars: {
					show : true,
					wovodat: true
				},
				dataType : "ds"
			};
			
			var option = {
				grid: {
					hoverable: true,
				},

				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to,
					autoscale: true,
					mode: "time",
					timeformat: "%Y-%m",
					//tickSize: [1, "month"]
					//timeformat: "%Y-%m-%d %H:%M:%S"
				},
				yaxis: {
					min: min_data,
					max: max_data,
					autoscale: true,
					//tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
				},

			};
			$.plot($("#data_series_graph1"), [param_ds] , option);
		}
	});*/
}