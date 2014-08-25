var ONE_YEAR =  ((new Date("0002-01-01T00:00:00")).getTime()-(new Date("0001-01-01T00:00:00")).getTime());
var ds_plot = [];
ds_plot.push("#data_series_graph1");
ds_plot.push("#data_series_graph2");
ds_plot.push("#data_series_graph3");
var ds_plot_graph = [];
vd_cavw = [];
var ds_overview_plot;
function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 20,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.8
    }).appendTo("body").fadeIn(200);
}

function convertDate(d) {
		var m = d.getMonth()+1;
		m = (m < 10)? '0' + m.toString() : m.toString();
		date = d.getDate();
		date = (date < 10)? '0' + date.toString() : date.toString();
		return (d.getFullYear() + '-' + m + '-' + date);
}

loadvd = function loadVolcano(args) {
	var data = args.data;
	var volcanoSelect = $("#volcano");
	volcanoSelect.empty();
	volcanoSelect.append(new Option("...", ""));
	for (var i = 0; i < data.length; i++) {
		var option = new Option(data[i]['vd_id'] + ". "+ data[i]['vd_name'], data[i]['vd_id']);
		option.setAttribute("cavw", data[i]['vd_cavw']);
		volcanoSelect.append(option);
		vd_cavw[data[i]['vd_id']] = data[i]['vd_cavw'];
	}
}
function getVdList () {
	loading = $.Deferred();
	DataPuller.getVolcanoList({handler: loadvd});
	test = "abc";
	loading.resolve();
	return loading;
}
$(document).ready(function () {

	test = "ab";
	getVdList().done( function () {
		console.log(test);
		//setTimeout(function() {$('#volcano').val("1056");	$('#volcano').trigger("change");}, 3000);
		console.log($('#volcano'));
	} ) ;
	//DataPuller.getVolcanoList({handler: loadvd}); 
	/*.done (function () {
		console.log($('#eruptionselect'));
		$('#eruptionselect').value = 1056;
		console.log($('#eruptionselect').value);
	});*/
	
	/*
	*	load the volcano list
	*
	*/
	
	
	/*
	*	when user select a volcano
	*/
	$("#volcano").change(function() {
		console.log($('#volcano'));
		eruptionSelect = $('#eruptionselect');
		eruptionSelect.empty();
		eruptionSelect.append(new Option("...", null));
		$('#data_series_checkbox').empty();
	
		var volcano = $("#volcano").val();
		console.log($("#volcano option:selected"));
		cavw = $("#volcano option:selected").attr("cavw");
		
		DataPuller.getEruptionList({vd_id: volcano, handler: plotEruption});
		DataPuller.getEruptionForecastList({vd_id: volcano, handler: plotEruptionForecast});
		DataPuller.getDataList({vd_id: volcano, vd_cavw: cavw, handler: plotOverview});
	});

	$("#eruptionselect").change(function() {
		if ($('#eruptionselect option:selected').text() === "...") {return 0;}

		stime = parseInt(eruptionSelect.val());
		ntime = {min: stime, max: stime + ONE_YEAR};

		$.extend(eruption_plot.getAxes().xaxis.options, ntime);
		eruption_plot.setupGrid();
		eruption_plot.draw();
		sync();
	});
	
	var sync = function () {
		stime = eruption_plot.getAxes().xaxis.options.min;
		etime = eruption_plot.getAxes().xaxis.options.max;
		ntime = {min: stime, max: etime};
		$.extend(eruption_forecast_plot.getAxes().xaxis.options, ntime);
		eruption_forecast_plot.setupGrid();
		eruption_forecast_plot.draw();
		if (ds_overview_plot) {
			$.extend(ds_overview_plot.getAxes().xaxis.options, ntime);
			ds_overview_plot.setupGrid();
			ds_overview_plot.draw();
			$.extend(ds_plot_graph[0].getAxes().xaxis.options, ntime);
			ds_plot_graph[0].setupGrid();
			ds_plot_graph[0].draw();
		}
	}
	
	$("#eruption_graph").bind("plotpan", sync);
	$("#eruption_graph").bind("plotzoom", sync);
});
