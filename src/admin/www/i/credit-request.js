$(document).ready(function(){    
    objectData = { image: 'CreditRequestImage', imageOwner: 'CreditRequest' };   
    
    $("#category").on("change", function() {
        $("#description-container").html($("#category option:selected").data('description'));
    });
});