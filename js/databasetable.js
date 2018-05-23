function generateTable(query) {
    $.post("dbquery.php", {
        query: query
    }, function(data,status) {
        return data;
    });
}