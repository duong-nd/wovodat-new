function convert(str) {
	var data = [];
	var stns = str.split(";");
	for(var i in stns) {
		temp = stns[i].split("&");
		stn = {
			type: temp[0].toLowerCase(),
			table: temp[1],
			code: temp[2],
			lat: temp[3],
			lon: temp[4],
		} 
		if (temp[5]) stn.component = temp[5];
		if (temp[0].length < 2) break;
		console.log(temp);
		//DataPuller.getStationData({sinfo: stn,handler: function (args) {data.push({	station: args.sinfo,	data_series : args.data,	});
		data.push({
			station: stn
		});		
	}
	return data;
}

function plotOverview(args) {
	ds_data = [];
	var data_stn = convert(args.data);
	
	data_series = [];
	
	var max_data = -1000;
	var min_data = 1000;
	
	window.ds_selection_box = $("#data_series_checkbox");
	
	$.each(data_stn, function(key, val) {
		console.log(key);		console.log(val);
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
	

	//initData();
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
			//min: min_data,	max: max_data,
			autoscale: true,
	//		tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
		},

	};
	
	function prepareData() {
		var data_series = [];
		for(var i in ds_data) {
			var d = ds_data[i].data;
			data_series[i] = [];
			for(var j in d) {
				ds = d[j];
				//ds.component_data = parseInt(ds.component_data);
				data_series[i].push([ds[0], ds[1], 0, ds.etime - ds[0],ds]);
			}
		}
		return data_series;
	}

	function redrawGraph() {
		var data = prepareData();
		var cnt = 0;
		param_dso = [];
		$("#data_series_overview").empty();
		for (i in ds_plot) {
			$(ds_plot[i]).empty();
		}
		
		for (i in data) {
			param_dso.push({
			data: data[i],
			lines: {
					show: true,
					wovodat: true
				},
				dataType: "dso",
			});
			cnt = cnt + 1;
		}
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
				//min: min_data,	max: max_data,
				autoscale: true,
				//tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
			},
		};
		if (param_dso.length > 0) 
			window.ds_overview_plot = $.plot($("#data_series_overview"), param_dso, option);
		
		
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
				//min: min_data,	max: max_data,
				autoscale: true,
		//		tickFormatter: function(val, axis) { return val < axis.max ? val.toFixed(2) : "VEI";}
			},
		};
		for (i in param_dso) {
			param = [];
			param.push({
				data : param_dso[i].data,
				bars : {show: true, wovodat:true},
				dataType: "ds",
			});
			
			ds_plot_graph[i] = $.plot($(ds_plot[i]), param,option_ds);
		}
	}
	
	function updateGraph(args) {
		console.log(args);
		ds_data.push({data: args.data[0], key : args.key});
		redrawGraph();
	}
	
	function plotAccordingToChoices() {
		var $this = $(this);
		if( $this.is(':checked') == true ) {
			DataPuller.getStationData({sinfo: data_stn[$this.attr("name")], key : $this.attr("name"), handler: updateGraph});
		} else {
			for (var i in ds_data) {
				if (ds_data[i].key == $this.attr("name")) {
					ds_data.splice(i,1);
					break;
				}
			}
			redrawGraph();
		}
	}
}
