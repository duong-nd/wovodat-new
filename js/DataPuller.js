function DataPuller() {
}

DataPuller.getVolcanoList = function(args) {
	var handler = args.handler;
	$.ajax({
		type: 	"POST",
		url: 	"api/",
		data: 	{data:"volcano_list"},
		dataType: "json"
	})
	.done(function(result) {
		args.data = result;
		handler(args);
	});
	
}

DataPuller.getEruptionList = function(args) {
	var vd_id = args.vd_id; 
	var handler = args.handler;

	$.ajax({
		type: "POST",
		url: "api/",
		data: { 
			data: "eruption_list", 
			vd_id: vd_id
		},
		dataType: "json"
	}).done(function(result) {
		args.data = result;
		handler(args);
	});
}

DataPuller.getEruptionForecastList = function(args) {
	var vd_id = args.vd_id;
	var handler = args.handler;
	$.ajax({
		type: "POST",
		url: "api/",
		data: { 
			data: "eruption_forecast_list", 
			vd_id: vd_id
		},
		dataType: "json"
	}).done(function(result) {
		args.data = result;
		handler(args);
	});
}

DataPuller.getDataList = function(args) {
	console
	var vd_id = args.vd_id;
	var cavw = args.vd_cavw;
	var handler = args.handler;
	$.ajax({
		type: "GET",
		url: "/php/switch.php?get=TimeSeriesForVolcano&cavw=" + cavw,
	}).done(function(result) {
		args.data = result;
		handler(args);
	});
}
/*
DataPuller.getDataList = function(args) {
	var vd_id = args.vd_id;
	var handler = args.handler;
	$.ajax({
		type: "POST",
		url: "api/",
		data: { 
			data: "data_series_list", 
			vd_id: vd_id
		},
		dataType: "json"
	}).done(function(result) {
		args.data = result;
		handler(args);
	});
}
*/
DataPuller.getStationData = function(args) {
	var handler = args.handler;
	stn = args.sinfo.station;
	urlstr = "/php/switch.php?get=StationData&type=" + stn.type +
				"&table=" + stn.table + "&code=" + stn.code;
	
	if (stn.component) urlstr = urlstr + "&component=" + stn.component;
	$.ajax({
		type: "GET",
		url: urlstr,
		dataType: "json"
	}).done(function(result) {
		args.data = result;
		handler(args);
	});
}