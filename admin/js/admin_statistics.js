google.charts.setOnLoadCallback(function() {
	var query = "SELECT tbl_registee_types.TypeName, COUNT(tbl_signups_18.RegisteeType) as `Number` FROM `tbl_signups_18` INNER JOIN tbl_registee_types ON tbl_signups_18.RegisteeType = tbl_registee_types.TypeID GROUP BY tbl_signups_18.RegisteeType";
	convertCSVToPieChart(query, "Types of Registrations", "statistics_typeNumbers", false);

	query = "SELECT tbl_companies.CompanyName, COUNT(tbl_signups_18.CompanyUnit) FROM `tbl_signups_18` INNER JOIN tbl_companies ON tbl_signups_18.CompanyUnit = tbl_companies.CompanyID WHERE tbl_signups_18.RegisteeType = 1 GROUP BY CompanyName";
	convertCSVToPieChart(query, "Kids per Company", "statistics_kidsPerCompany", false);

	query = "SELECT tbl_companies.CompanyName, COUNT(tbl_signups_18.CompanyUnit) FROM `tbl_signups_18` INNER JOIN tbl_companies ON tbl_signups_18.CompanyUnit = tbl_companies.CompanyID WHERE tbl_signups_18.RegisteeType >= 2 AND tbl_signups_18.RegisteeType <= 3 GROUP BY CompanyName";
	convertCSVToPieChart(query, "Leaders (and Parent Help) per Company", "statistics_leadersPerCompany", false);


});
