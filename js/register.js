$(document).ready(function() {	
	$(".invalid").change(function() {
		$(this).removeClass("invalid");
	});
	
	$(".invalid-name").change(function() {
		$(this).removeClass("invalid-name");
	});
	
	$(".invalid-email").change(function() {
		$(this).removeClass("invalid-email");
	});
	
	$(".invalid-company").change(function() {
		$(this).removeClass("invalid-company");
	});
	
	$(".invalid-phone").change(function() {
		(".invalid-phone").removeClass("invalid-company");
	}); 

	//This is for browsers that don't support date fields. This used to only be FireFox, however
	//it seems modern versions of FireFox now support it. Even though this should never be an
	//issue, it's here just in case someone is using an outdated version (update you pleb!!!)
    if (!Modernizr.inputtypes.date) {
    	console.log("Running on old version of firefox so... lets hope this works.");
        $('input[type=date]').datepicker({
        	dateFormat : 'yy-mm-dd'
        });
    } else {
    	console.log("Cool, you support date types. Thanks for using a browser that doesn't break standards. :)");
    }
});

function submitForm() {
	removeHardenedState();

	setTimeout(function() {
		document.getElementById("register_form_form").submit();
	}, 50);	
	
	
}

function rand(array) {
	var max = array.length;
	return array[Math.floor(Math.random() * max)];
}

/**
 * Unused; perhaps for another year
 * @returns {string} The generated codename
 */
function generateCodeName() {
	var prefixes = ["Titanium", "Steel", "Golden", "Silver", "Palladium", "Lightning", "Crimson", "Ruby", "Emerald", "Sapphire", "Platinum",
		"Incredible", "Silent", "Futuristic", "Speedy", "Mysterious", "Faithful", "Charming", "Immortal", "Energized", "Energetic", "Magnificent",
		"Electric", "Almighty", "Majestic", "Fearsome", "Intelligent"];

	var suffixes = ["Sparrow", "Panda", "Pukeko", "Otter", "Badger", "Chinchilla", "Crane", "Dolphin", "Falcon", "Herring", "Jaguar", "Raven",
		"Salamander", "Wolverine", "Rhino", "Husky", "Boar", "Wanderer", "Dragon", "Wombat"];


	return rand(prefixes) + " " + rand(suffixes);
}

function removeHardenedState() {
	$("#form-agentID").removeAttr("disabled");
	$("#form-agentID-button").attr("disabled", "disabled");
	
	console.log("Removed disabled agentID attribute!");
}

console.log("Done loading JS!")