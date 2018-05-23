$(document).ready(function() {
    $.post("dbquery.php", {
        query: "SELECT * FROM tbl_logins"
    }, function(data,status) {
        $("#tableWrapper_db_registeredAdminUsers").html(data);
    });
});