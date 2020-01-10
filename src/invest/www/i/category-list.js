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
                    } else {
                        let errors = [];
                        if (result.errors) { $.each(result.errors, function (index, error) { errors.push(error); });
                        } else { errors.push('е удалось сохранить сортировку, попробуйте еще раз чуть позже&hellip;'); }
                        $("#alert-block-content").html(errors.join('<br />'));
                        $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
                    }
                },"json"
            ).fail(function() {
                $("#alert-block-content").html('Не удалось сохранить сортировку, попробуйте еще раз чуть позже&hellip;');
                $("#alert-block").show("swing", function() { clearTimeout(alertTimer); alertTimer = setTimeout(function() { $("#alert-block").hide("swing"); }, 3000); });
            });
        },
        helper: function(e, ui) {
            ui.children().each(function() { $(this).width($(this).width()); });
            return ui;
        }
    });
});