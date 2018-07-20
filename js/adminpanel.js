$(document).ready(function() {
    $($(".page-content-that-i-must-find").get(0)).addClass("active");
    $($(".nav-tab-that-i-must-find").get(0)).addClass("active");

	$.post("dbquery.php", {
		query: "SELECT * FROM tbl_companies",
		csv: true,
		noheaders: true
	}, function(data,status) {
		var html = "";
		var dropDownHTML = "";
		for (var i in data.split("\r\n")) { //For every row
			var s = data.split("\r\n")[i];
			html = html + "<option value=\"" + s.split(",")[0] + "\">" + s.split(",")[1] + "</option>";
			dropDownHTML = dropDownHTML + "<a class='dropdown-item' href='#' value='" + s.split(",")[0] + "'>" + s.split(",")[1] + "</a>";
		}
		$(".company-selector").each(function() {
			$(this).html(html);
			if ($(this)[0].hasAttribute("data-company-callback")) {
				var callbackString = $(this).attr("data-company-callback");
				window[callbackString](); //Call the function if it has a callback
			}
		})

		$(".company-dropdown-selector").each(function() {
			$(this).html(dropDownHTML);
			if ($(this)[0].hasAttribute("data-company-callback")) {
				var callbackString = $(this).attr("data-company-callback");
				window[callbackString](); //Call the function if it has a callback
				console.log("Called the callback function")
			}
		});
	});
});