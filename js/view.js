$(document).ready(function() {
    if (query == "") return; //An error occured

    $("#downloadCSV").click(function() {
        $.post("dbquery.php", {
            query: query,
            csv: true
        }, function(data,status) {
            let csvContent = "data:text/csv;charset=utf-8," + data;
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
        });
    });

    $.post("dbquery.php", {
        query: query,
    }, function(data,status) {
        $("#databaseTable").html(data);
    });
});