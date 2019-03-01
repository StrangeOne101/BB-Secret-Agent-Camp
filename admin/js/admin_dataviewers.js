$(document).ready(function() {
	$("#dataViewerModalConfirm").click(function() {
		var sendObject = {};
		sendObject.query = $("#dataViewerAddQueryType")[0].value;
		sendObject.title = $("#dataViewerAddTitle")[0].value;

		if ($("#dataViewerAddQueryType")[0].value == 1) {
			sendObject.parameters = $("#dataViewerAddCompany")[0].value;
		}

		$("#dataViewerModalConfirm").attr("disabled", "disabled");
		$("#dataViewerModalConfirm").text("Sending...");

		$.post("createViewer.php", sendObject, function(data, status) {
			if (data.startsWith("<h4>Error")) {
				showViewerModal("Error", data);
			} else {
				$("#dataViewerModal").modal("hide");
				showViewerModal("Success!", "A link has been created! Click <a href=\"" + data + "\">here</a> to view it!");
				updateLinks();
			}

			$("#dataViewerModalConfirm").removeAttr("disabled");
			$("#dataViewerModalConfirm").text("Create Link!");
		});

	});

	updateLinks();

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

function updateLinks() {
	$.post("dbquery.php", {
		query: "SELECT Title, Token AS Link FROM tbl_tokens"
	}, function(data,status) {
		if (data["response-code"] != 200) {
			console.log(data["response-code"] + ": " + data["message"]);
			return;
		}

		$("#dataViewerTable").html(data["data"]);

		var table = $("#dataViewerTable").children("table")[0];

		$(table).children("tbody").children("tr").each(function() {
			var index = 0;
			$(this).children("td").each(function () {
				if (index++ == 2) {
					var url = document.location.origin + "/admin/view.php?token=" + this.innerHTML;
					$(this).html("<a target=\"_blank\" href='" + url + "'>" + url + "</a>");
					console.log(this);
				}
			});
		});
	});
}

function showViewerModal(title, message) {
	$("#adminDataviewer_alert_title").html(title);
	$("#adminDataviewer_alert_body").html(message);
	$("#adminDataviewer_alert").modal();

}