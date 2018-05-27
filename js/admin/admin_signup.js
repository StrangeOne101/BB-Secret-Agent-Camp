$(document).ready(function() {
    $.post("dbquery.php", {
        query: "SELECT * FROM tbl_logins"
    }, function(data,status) {
        $("#tableWrapper_db_registeredAdminUsers").html(data);
    });

    $("#submitAdminSignup").click(function(event) {
        $("#submitAdminSignup").attr("disabled", "disabled");
        var firstname = $("#adminRegister_firstname").val();
        var lastname = $("#adminRegister_lastname").val();
        var email = $("#adminRegister_email").val();
        var permission = $("#adminRegister_perm").val();

        $.post("createLogin.php", {
            firstname: firstname,
            lastname: lastname,
            email: email,
            permission: permission
        }, function(data, status) {
            if (data.length > 0) {
                $("#modalTitle").text("Error");
                $("#modalBody").html(data);
                $("#modalDisplay").modal();
            } else {
                $(".adminSignupInput").val("");
                $("#adminRegister_perm").val("64");
            }
        });
    });

    $(".adminSignupInput").change(aschange);
    $(".adminSignupInput").keyup(aschange);
});

function aschange() {
    var notnull = true;
    $(".adminSignupInput").each(function() {
        if (this.value.length == 0 && !notnull) notnull = true;
    });

    if (notnull) {
        $("#submitAdminSignup").removeAttr("disabled");
    } else {
        $("#submitAdminSignup").attr("disabled", "disabled");
    }
}