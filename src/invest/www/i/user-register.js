$(document).ready(function(){
    let cacheName = {};
    
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
                $("#alert-block").hide("swing");
                
                if (result.userExists) {
                    $("#warning-block").show("swing");
                } else if (result.success) {
                    $("#warning-block").hide("swing");
                    $("#code-block").show("swing", function() { $("#code").focus(); });
                    showSuccess('SMS сообщение с кодом подтверждения отправлено');
                } else {
                    let errors = [];
                    if (result.errors) { $.each(result.errors, function (index, error) { errors.push(error); });
                    } else { errors.push('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;'); }
                    $("#warning-block").hide("swing");
                    $("#alert-block").hide("swing");
                    showError(errors);
                }
            },"json"
        ).fail(function() {
            $("#alert-block").hide("swing");
            $("#warning-block").hide("swing");
            showError('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
        });
    });
    
    $("#code-confirm-button").on("click", function() {
        $.post("/ajax/user-confirm-code.json", {'uuid': $("#uuid").val()},
            function(result) {
                $("#alert-block").hide("swing");
                
                if (result.success) {
                    $("#code-block").show("swing", function() { $("#code").focus(); });
                    showSuccess('SMS сообщение с кодом подтверждения отправлено');
                } else {
                    let errors = [];
                    if (result.errors) { $.each(result.errors, function (index, error) { errors.push(error); });
                    } else { errors.push('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;'); }
                    showError(errors);
                }
            },"json"
        ).fail(function() {
            $("#alert-block").hide("swing");
            showError('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
        });
    });
});