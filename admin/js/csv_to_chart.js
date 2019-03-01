google.charts.load('current', {'packages':['corechart']});

/**
 * Convert a database query into a pie chart
 * @param query The query. Must only return 2 columns.
 * @param title The title of the chart.
 * @param chartDivId The element to load the chart into after building
 * @param threeD If the chart should be displayed in 3D.
 * @param type The type of chart. Should be "pie" or "line" currently.
 */
function convertCSVToChart(query, title, chartDivId, threeD, type) {
	if (typeof threeD === 'undefined') {
		threeD = false;
	}
	$.post("dbquery.php", {
		query: query,
		csv: true,
	}, function(data, status) {
		if (data["response-code"] != 200) { //If the data returned an error, just display the error in the html
			console.log(data["response-code"] + ": " + data["message"]);
			$("#" + chartDivId).html("<h4>Error " + data["response-code"] + ": " + data["message"] + "</h4>");
			return;
		}

		var headers = data["data"].split("\n")[0]; //The first line is the column names
		var types = data["data"].split("\n")[1];   //The second line is the data, and we use this to test types
		var dataTable = new google.visualization.DataTable();

		//Loop through all columns and add them to the dataTable (to give to the pie chart)
		for (var i = 0; i < types.split(",").length; i++) {
			var variable = types.split(",")[i];
			var name = headers.split(",")[i];

			var stringOrNo = isNaN(variable) ? "string" : "number";

			if (stringOrNo == "string" && new Date(variable) != "Invalid Date") {
				stringOrNo = "date";
			}
			console.log("i = " + i + " , var = " + variable + " , name = " + name + " , type = " + stringOrNo)

			dataTable.addColumn(stringOrNo, name);
		}

		var rows = [];
		for (var i = 1; i < data["data"].split("\n").length; i++) { //Loop through all lines
			var row = [];
			var line = data["data"].split("\n")[i]; //Current line

			for (var j = 0; j < line.split(",").length; j++) { //Loop through all values and add them to the array
				var value = line.split(",")[j];
				if (!isNaN(value)) { //If the value isn't a string, turn it back into a number. Charts are picky with typing.
					value = Number(value);
				} else if (new Date(value) != "Invalid Date") {
					value = new Date(value);
				}
				row.push(value);
			}

			rows.push(row); //Once the row array is complete, add it to the list of rows
		}

		dataTable.addRows(rows);

		var options = {
			is3D: threeD,
			title: title,
			chartArea: {  width: "70%", height: "70%" }
		};

		var chart;
		if (type.toLowerCase() == "pie") {
			chart = new google.visualization.PieChart(document.getElementById(chartDivId));
		} else {
			chart = new google.visualization.LineChart(document.getElementById(chartDivId));
		}
		chart.draw(dataTable, options);

	});
}

function convertCSVToPieChart(query, title, chartDivId, threeD) {
	convertCSVToChart(query, title, chartDivId, threeD, "pie");
}

function convertCSVToLineChart(query, title, chartDivId, threeD) {
	convertCSVToChart(query, title, chartDivId, threeD, "line");
}