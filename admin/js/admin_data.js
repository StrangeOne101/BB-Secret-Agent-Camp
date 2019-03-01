var registrationDataMD5 = "";

$(document).ready(function() {
    updateRegistrationData();

	updateMD5();

    setInterval(function() {
		updateMD5(function() {
			updateRegistrationData(); //If the data has been updated, refresh the table
		});
	}, 1000 * 60 * 10);
});

function updateRegistrationData() {
	$.post("dbquery.php", {
		queryno: 0 //Common query no. 1 - Get all registration data
	}, function(data,status) {
		if (data["response-code"] != 200) {
			console.log(data["response-code"] + ": " + data["message"]);
			return;
		}

		$("#tableWrapper_registeredUsers").html(data["data"]);

		var table = $("#tableWrapper_registeredUsers").children("table")[0];
		shortenLongFields(table);
		expandFieldWidth(table, "MedicalDetails", 200);
		expandFieldWidth(table, "FoodDetails", 200);
		expandFieldWidth(table, "Address", 200);
		expandFieldWidth(table, "Company", 150);
	});
}

function updateMD5(callback) {
	$.post("dbquery.php", {
		queryno: 0,
		refresh: true
	}, function(data) {
		if (data["response-code"] != 200) {
			console.log(data["response-code" + ": " + data["message"]]);
			return;
		}
		var json = JSON.parse(data["data"]);
		if (json.hashcode != registrationDataMD5) {
			var oldData = registrationDataMD5;   //We store the old data before checking it so we can
			registrationDataMD5 = json.hashcode; //call the callback after it's already been updated

			if (oldData != "" && typeof callback !== 'undefined') { //If the old data is nothing, we are setting it first time.
				callback();
			}
		}
	})
}