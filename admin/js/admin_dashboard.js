google.charts.setOnLoadCallback(function() {
	var query = "SELECT tbl_companies.CompanyName AS `Company`, Count(tbl_signups_19.CompanyUnit) AS `Count` FROM `tbl_signups_19` INNER JOIN `tbl_companies` ON tbl_signups_19.CompanyUnit = tbl_companies.CompanyID WHERE tbl_signups_19.RegisteeType < 5 GROUP BY Company ORDER BY Company";
	convertCSVToPieChart(query, "Total Registrations", "recentStatistics_total", true);

	var chartQuery = "SELECT DateRegistered AS Date, Count(DateRegistered) AS `Number of Registrations` FROM `tbl_signups_19` GROUP BY Date ORDER BY Date";
	convertCSVToLineChart(chartQuery, "Registration Count Per Day", "recentStatistics_numberOverTime", false);

});

$.post("dbquery.php", {
	queryno: 2 //Common query no. 3 - Get all recent registration data
}, function(data,status) {
	$("#registrationData_recents").html(data.data);

	var table = $("#registrationData_recents").children("table")[0];
	shortenLongFields(table);
	expandFieldWidth(table, "MedicalDetails", 200);
	expandFieldWidth(table, "FoodDetails", 200);
	expandFieldWidth(table, "Address", 200);
	expandFieldWidth(table, "Company", 150);
});