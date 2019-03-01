$(document).ready(function() {
	if (typeof database === 'undefined') {
		return;
	}
    $("#downloadCSV").click(function() {
    	var sendObject = {...database}; //Clone the database object
    	sendObject.csv = true; //Set CSV to true

        $.post("dbquery.php", sendObject, function(data,status) {
        	if (data["response-code"] != 200) {
        		alert("Error " + data["response-code"] + ": " + data["message"]);
        		return;
			}

            let csvContent = "data:text/csv;charset=utf-8," + data["data"];
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            let date = new Date();
            let dateStr = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear() + "--";
            let AMPM = date.getHours() > 12 ? "PM" : "AM";
            let hour = date.getHours() % 12;
            hour = hour == 0 ? 12 : hour;
            dateStr = dateStr + hour + "-" + date.getMinutes() + AMPM;
            link.setAttribute("download", "SpaceCampData-" + dateStr + ".csv");
            link.innerHTML= "Click Here to download";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

	var table = $("#databaseTable").children("table")[0];
	shortenLongFields(table);
	expandFieldWidth(table, "MedicalDetails", 200);
	expandFieldWidth(table, "FoodDetails", 200);
	expandFieldWidth(table, "Address", 200);

    /*$.post("dbquery.php", {
        query: query,
    }, function(data,status) {
        $("#databaseTable").html(data);
    });*/


});