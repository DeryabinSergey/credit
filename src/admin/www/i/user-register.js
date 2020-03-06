$(document).ready(function(){
    let cacheName = {};
    
    let alertTimer = null;
    $('#phone').mask('(000) 000-00-00');
    
    $("#name").autocomplete({
        minLength: 3,
        source: function( request, response ) {
            let term = request.term;
            if ( term in cacheName ) {
                response( cacheName[ term ] );
                return;
            }
 
            $.getJSON("/ajax/user-name.json", request, function( data, status, xhr ) {
                if (data.list) {
                    cacheName[term] = data.list;
                    response(data.list);
                }
            });
        }
    });
    
    $("#code-button").on("click", function() {
        $.post("/ajax/user-register-code.json", {'phone': $("#phone").val()},
            function(result) {
                if (result.userExists) {
                    $("#warning-block").show("swing");
                } else if (result.success) {
                    $("#warning-block").hide("swing");
                    $("#code-block").show("swing", function() { $("#code").focus(); });
                } else {
                    let errors = [];
                    if (result.errors) { $.each(result.errors, function (index, error) { errors.push(error); });
                    } else { errors.push('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;'); }
                    $("#alert-block-content").html(errors.join('<br />'));
                    $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
                }
            },"json"
        ).fail(function() {
            $("#alert-block-content").html('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
            $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
        });
    }); 
    
    $("#code-confirm-button").on("click", function() {
        $.post("/ajax/user-confirm-code.json", {'uuid': $("#uuid").val()},
            function(result) {
                if (result.userExists) {
                    $("#warning-block").show("swing");
                } else if (result.success) {
                    $("#code-block").show("swing", function() { $("#code").focus(); });
                } else {
                    let errors = [];
                    if (result.errors) { $.each(result.errors, function (index, error) { errors.push(error); });
                    } else { errors.push('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;'); }
                    $("#alert-block-content").html(errors.join('<br />'));
                    $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
                }
            },"json"
        ).fail(function() {
            $("#alert-block-content").html('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
            $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
        });
    });   
});