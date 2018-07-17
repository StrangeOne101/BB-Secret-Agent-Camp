function generateTable(query) {
    $.post("dbquery.php", {
        query: query
    }, function(data,status) {
        return data;
    });
}


function shortenLongFields(tableElement, length) {
	if (typeof length === 'undefined') {
		length = 50;
	}

	$(tableElement).children("tbody").children("tr").each(function() {
		$(this).children("td").each(function() {
			if (this.innerHTML.length >= length) {
				$(this).attr("shortened", "true");
				$(this).attr("value", this.innerHTML);
				$(this).attr("title", "Click to expand");
				this.innerHTML = this.innerHTML.substr(0, length) + "...";

				$(this).click(function() {
					if ($(this).attr("shortened") == "true") {
						$(this).attr("shortened", "false");
						$(this).attr("title", "Click to retract");
						this.innerHTML = $(this).attr("value");
					} else {
						$(this).attr("shortened", "true");
						$(this).attr("title", "Click to expand");
						this.innerHTML = this.innerHTML.substr(0, length) + "...";
					}
				})
			}
		});
	});
}

function expandFieldWidth(table, field, width) {
	$(table).children("thead").children("tr").each(function() {
		$(this).children("th").each(function() {
			if (this.innerHTML.toLowerCase() == field.toLowerCase()) {
				$(this).attr("style", "min-width: " + width + "px");
			}
		});
	});
}