$(document).ready(function() {
    $(".selectpicker").children("option").each(function() {
        console.log(this);
      $(this).change(function() {
          $(this).addClass("active-tick");
      });
    });

    $.post("dbquery.php", {
        query: "SELECT * FROM tbl_companies",
        csv: true,
        noheaders: true
    }, function(data,status) {
        var html = "";
        for (var i in data.split("\r\n")) { //For every row
            var s = data.split("\r\n")[i];
            html = html + "<option value=\"" + s.split(",")[0] + "\">" + s.split(",")[1] + "</option>";
        }
        $("#emailToCompany").html(html);
    });
});