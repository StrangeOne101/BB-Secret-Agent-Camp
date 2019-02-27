/***
 * Preloads an image by sending an HTTP GET request to
 * the image to get the client to cache it
 * @param url
 */
function preloadImage(url) {
	if (url.endsWith(".png") || url.endsWith(".jpg") || url.endsWith(".jpeg")) {
		console.log("Preloading " + url);
		$.get(url);
	}
}


$(document).ready(function() {

	//On mouse over...
	$("a.photo-link").mouseenter(function() {
		$(this).attr("data-hover", ""); //Mark the element as being hovered over
		setTimeout(() => { //Start a timer for 100ms...
			if ($(this)[0].hasAttribute("data-hover") && !$(this)[0].hasAttribute("data-preloaded")) { //and if it still has the hover marking...
				var url = $(this).attr("href");

				if (url != null && url != "") {
					preloadImage(url); //they are still on the image, so preload in case they click.
					$(this).attr("data-preloaded", ""); //Mark as preloaded
				}

				$(this).removeAttr("data-hover"); //Remove the hover marking
			}
		}, 180);
	});

	$("a.photo-link").mouseleave(function() { //When they leave, remove the hover marking
		$(this).removeAttr("data-hover");
	});

	$(".zoom").hover(function() {
		$(this).addClass('transition');
	}, function() {
		$(this).removeClass('transition');
	});

	//Zoom effect
	$(".fancybox").fancybox({
		openEffect: "fade",
		closeEffect: "fade",
		helpers : {
			overlay : {
				css : {
					'background' : 'rgba(0, 0, 0, 0.95)'
				}
			}
		},
		/*beforeShow : function() {
			var alt = this.element.find('img').attr('alt');

			this.inner.find('img').attr('alt', alt);

			this.title = alt;
		}*/
	});


});