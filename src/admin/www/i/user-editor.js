$(document).ready(function(){
    $("#ban").on("change", function() {
        if ($("#ban").prop('checked')) {
            $("#forceBan").val(1);
            $("#ban-context").show("blind", {}, 500);
        } else {
            $("#forceBan").val(0);
            $("#ban-expire-text").hide();
            $("#ban-context").hide("blind", {}, 500);
        }
    });
});