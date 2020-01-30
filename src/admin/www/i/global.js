$(document).ready(function(){
    $(".table-responsive .selectpicker").each(function(i, el) {
        let init = false;
        $(el).on('show.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            $('.table-responsive').css( "overflow", "inherit" );
        });
        $(el).on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            $('.table-responsive').css( "overflow", "auto" );
            if (init) { 
                $('#filter-form').submit(); 
            } else {
                init = true;
            }
        });
    });
});