$(document).ready(function() {
    $.post("dbquery.php", {
        queryno: 0 //Common query no. 1 - Get all registration data
    }, function(data,status) {
        $("#tableWrapper_registeredUsers").html(data);
    });
});