$(document).ready(function() {
    $(".selectpicker").children("option").each(function() {
        console.log(this);
      $(this).change(function() {
          $(this).addClass("active-tick");
      });
    });


});

function emailCompanyLoadCallback() {
	console.log("Callback function called")
	$(".company-dropdown-selector").children("a").click(function(event) {
		event.preventDefault();
		$(this).toggleClass("active-tick");
		console.log(this);
	})
}