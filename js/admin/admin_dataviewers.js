$(document).ready(function() {
	$("#dataViewerModalConfirm").click(function() {

	});

	var changeFunc = function() {
		if ($("#dataViewerAddTitle")[0].value == "" || $("#dataViewerAddQueryType")[0].value == -1) {
			$("#dataViewerModalConfirm").attr("disabled", "disabled");
		} else {
			$("#dataViewerModalConfirm").removeAttr("disabled");
		}
	}

	$(".dataViewerForm").change(changeFunc);
	$(".dataViewerForm").keydown(changeFunc);

	$("#dataViewerAddQueryType").change(function() {
		if ($("#dataViewerAddQueryType")[0].value == 1) { //Company selected
			$(".dataViewerAddCompanySelector").removeClass("invisible"); //Make the company field visible
		} else if (!$(".dataViewerAddCompanySelector").hasClass("invisible")) {
			$(".dataViewerAddCompanySelector").addClass("invisible"); //Do the reverse
		}
	});
});