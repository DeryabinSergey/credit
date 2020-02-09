$(document).ready(function(){
    let hasTableHover = false,
        hasTableStripped = false,
        alertTimer = null;
        
    $('#sortable-table tbody').sortable({
        axis: 'y',
        placeholder: "ui-state-highlight",
        handle: '.handle',
        start: function(event, ui) {
            hasTableHover = $("#sortable-table").hasClass("table-hover");
            hasTableStripped = $("#sortable-table").hasClass("table-striped");
            $("#sortable-table").removeClass("table-hover").removeClass("table-striped");
        },
        stop: function( event, ui ) { 
            if (hasTableHover) { $("#sortable-table").addClass("table-hover"); }
            if (hasTableStripped) { $("#sortable-table").addClass("table-striped"); }
            let ids = [];
            for(let i = 0; i < event.target.rows.length; i++) {
                ids.push(event.target.rows[i].id.substr(3));
            }
            $.post("/ajax/category-sort.json", {'ids': ids},
                function(result) {
                    if (result.success) {
                        showImageUploaded('Сортировка категорий сохранена');
                    } else {
                        showError(result.errors ? result.errors : 'Не удалось сохранить сортировку, попробуйте еще раз чуть позже&hellip;');                        
                    }
                },"json"
            ).fail(function() {
                showError('Не удалось сохранить сортировку, попробуйте еще раз чуть позже&hellip;');
            });
        },
        helper: function(e, ui) {
            ui.children().each(function() { $(this).width($(this).width()); });
            return ui;
        }
    });
});