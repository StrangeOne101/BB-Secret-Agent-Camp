function aa() { //Check function
    if ($("#passwordField2")[0].value != "") { //If they have started typing in field no 2.
        if ($("#passwordField2")[0].value != $("#passwordField1")[0].value) { //If the passwords are different
            $("#submitme").attr("disabled", "disabled");
            $("#makesureyoutypethemright").text("Passwords must be the same");
        } else { //They are correct
            $("#submitme").removeAttr("disabled");
            $("#makesureyoutypethemright").text(" ");
        }
    }
}

$(document).ready(function() {
    $(".password-field").keyup(function() {
        aa();
    });

    $(".password-field").change(function() {
        aa();
    });
});

