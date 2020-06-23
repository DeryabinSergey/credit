$(document).ready(function() {
    let cacheName = {};
    let cacheOgrn = {};
    $('#phone').mask('(000) 000-00-00');
    
    $('#summ').mask('000 000 000 000 000', {reverse: true});
    $('#profit').mask('000 000 000 000 000', {reverse: true});
    $('#passport').mask('00 00 000000');
    $('#birthDate').mask(
        'A0.B0.CD00',
        {
            translation: {
                'A': { pattern: /[0-3]/, optional: false },
                'B': { pattern: /[0-1]/, optional: false },
                'C': { pattern: /[1-2]/, optional: false },
                'D': { pattern: /[0,9]/, optional: false }
            }
        }
    );
    
    objectData = {
        image: 'CreditRequestImage',
        imageOwner: 'CreditRequest'
    };
    
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
    
    $("#ogrn").autocomplete({
        minLength: 3,
        source: function( request, response ) {
            let term = request.term;
            if ( term in cacheOgrn ) {
                response( cacheOgrn[ term ] );
                return;
            }
            request.type = $("#type").val();
 
            $.getJSON("/ajax/credit-request-ogrn.json", request, function( data, status, xhr ) {
                if (data.list) {
                    cacheOgrn[term] = data.list;
                    response(data.list);
                }
            });
        },
        select: function( event, ui ) { $("#name").val(ui.item.name); }        
    });   
    
    $("#category").on("change", function() {
        if ($("#category option:selected").data('pledgeRequired')) {
            if ($("#text-row").is(":hidden")) {
                $("#text-row").show("slide");
            }
        } else {
            if (!$("#text-row").is(":hidden")) {
                $("#text-row").hide("blind");
                $("#text").val("");
            }            
        }
    });
    
    $("#type").on("change", function() { 
        
        if ($(this).val() == 3) {
            $("#ogrn-row").show("slide");
            $("#ogrn").val("");
            $("#name-row").show("slide");
            $("#name-title").html("Название");
            $("#name-description").html("Полное название организации");
            $("#name").val("");
            $("#name").prop("readonly", true);
            $("#birthDate-row").hide("blind");
            $("#birthDate").val("");
            $("#passport-row").hide("blind");
            $("#passport").val(""); 
        } else if ($(this).val() == 2) {
            $("#ogrn-row").show("slide");
            $("#ogrn").val("");
            $("#name-row").show("slide");
            $("#name-title").html("ФИО");
            $("#name-description").html("Укажите ФИО полностью как в паспорте");
            $("#name").val("");
            $("#name").prop("readonly", true);
            $("#birthDate-row").show("slide");
            $("#birthDate").val("");  
            $("#passport-row").show("slide");
            $("#passport").val("");         
        } else if ($(this).val() == 1) {
            $("#ogrn-row").hide("blind");
            $("#ogrn").val("");
            $("#name-row").show("slide");
            $("#name-title").html("ФИО");
            $("#name-description").html("Укажите ФИО полностью как в паспорте");
            $("#name").val("");
            $("#name").prop("readonly", false);
            $("#birthDate-row").show("slide");
            $("#birthDate").val("");
            $("#passport-row").show("slide");
            $("#passport").val("");
        } else {
            $("#ogrn-row").hide("blind");
            $("#ogrn").val("");
            $("#name-row").hide("blind");
            $("#name").val("");
            $("#birthDate-row").hide("blind");
            $("#birthDate").val("");
            $("#passport-row").hide("blind");
            $("#passport").val("");
        }
        
        
        
    });
    
    $("#code-button").on("click", function() {
        $.post("/ajax/credit-request-code.json", {'phone': $("#phone").val()},
            function(result) {
                $("#alert-block").hide("swing");
                if (result.success) {
                    showSuccess('SMS сообщение с кодом подтверждения отправлено');
                    $("#code-block").show("swing", function() { $("#code").focus(); });
                } else {
                    showError(result.errors ? result.errors : 'Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
		    $("#phone").removeClass('is-valid').addClass('is-invalid');
                }
            },"json"
        ).fail(function() {
            $("#alert-block").hide("swing");
            showError('Не удалось отправить SMS, попробуйте еще раз чуть позже&hellip;');
	    $("#phone").removeClass('is-valid').addClass('is-invalid');
        });
    });
    
    $("form input").each(function(i, el) {
	if ($(this).attr('type') != 'hidden' && $(this).attr('type') != 'file') {
	    $(this).on("focus", function() { $(this).removeClass('is-valid').removeClass('is-invalid'); });
	    $(this).on("blur", function() { $(this).addClass('is-' + ($(this).val() ? '' : 'in')+'valid'); });
	}
    });	
});