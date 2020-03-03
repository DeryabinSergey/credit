$(document).ready(function() {    
    $('#summ').mask('000 000 000 000 000', {reverse: true});
    $('#percents').mask('000 000 000 000 000.00', {reverse: true});
    
    $('#investSumm').mask('000 000 000 000 000', {reverse: true});
    $('#investPercents').mask('000 000 000 000 000.00', {reverse: true});
    
    $("#investRequestButton").on('click', function() {
        $('#investRequestCont').show("slide");
        $('#offerCont').hide("blind");
    });
    
    $("#investRequest").on('click', function() {
        $('#form').attr('action', '#investOffers');
    });
    
    $("#offerButton").on('click', function() {
        $('#offerCont').show("slide");
        $('#investRequestCont').hide("blind");
    });
    
    $("#offer").on('click', function() {
        $('#form').attr('action', '#offers');
    });
});