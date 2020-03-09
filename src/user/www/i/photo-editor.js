$(function () {
    $("#related-images").sortable({distance: 10, placeholder: "card-placeholder",
        stop: function (event, ui) {
            var order = [];
            $('#related-images .card').each(function() { order.push($(this).attr("id").substr(9)); });            
            $.post("/ajax/photo-sort.json", {'object': objectData.image, 'ids': order, 'securityCode': $("#security-code").val()},
                function(result) {
                    if (result.success) { 
                        showImageUploaded("Сортировка изображений сохранена");
                    } else {
                        showError(result.errors ? result.errors : "При сохранении сортировки, попробуйте, пожалуйста, еще раз чуть позже");
                    }
                },"json"
            ).fail(function() { showError("При сохранении сортировки, попробуйте, пожалуйста, еще раз чуть позже"); });
        }
    });
    
    imageAction = function(e) {
        var item = $(e.target);
        if (item.hasClass("rotate-cw")) {
            var id = parseInt(item.parent().attr("id").substr(10));
            $("#image-id-"+id).addClass("loading");
            $.post("/ajax/photo-rotate.json", {'id': id, 'object': objectData.image, 'cw': 1, 'securityCode': $("#security-code").val()}, function(result) {
                    if (result.id && result.url) {
                        $("#image-id-"+result.id).css("backgroundImage", "url("+result.url+")");
                    } else {
                        showError(result.errors ? result.errors : "При повороте изображения, попробуйте, пожалуйста, еще раз чуть позже");
                    }
                    $("#image-id-"+id).removeClass("loading");
                },"json")
                .fail(function() {
                    showError("При повороте изображения, попробуйте, пожалуйста, еще раз чуть позже");
                    $("#image-id-"+id).removeClass("loading");
                });
        } else if (item.hasClass("rotate-acw")) {
            var id = parseInt(item.parent().attr("id").substr(11));
            $("#image-id-"+id).addClass("loading");
            $.post("/ajax/photo-rotate.json", {'id': id, 'object': objectData.image, 'securityCode': $("#security-code").val()}, function(result) {
                    if (result.id && result.url) {
                        $("#image-id-"+result.id).css("backgroundImage", "url("+result.url+")");
                    } else {
                        showError(result.errors ? result.errors : "При повороте изображения, попробуйте, пожалуйста, еще раз чуть позже");
                    }
                    $("#image-id-"+id).removeClass("loading");
                },"json")
                .fail(function() {
                    showError("При повороте изображения, попробуйте, пожалуйста, еще раз чуть позже");
                    $("#image-id-"+id).removeClass("loading");
                });
        } else if (item.hasClass("delete")) {
            if (confirm("Удалить изображение?")) {
                var id = parseInt(item.parent().attr("id").substr(7));
                $("#image-id-"+id).addClass("loading");
                $.post("/ajax/photo-delete.json", {'id': id, 'object': objectData.image, 'securityCode': $("#security-code").val()}, function(result) {
                        if (result.id) {
                            $("#image-id-"+result.id).hide("blind", 800, function() { 
                                $("#image-id-"+result.id).remove()
                            });
                        } else {
                            showError(result.errors ? result.errors : "При удалении файла, попробуйте, пожалуйста, еще раз чуть позже");
                            $("#image-id-"+id).removeClass("loading");
                        }
                    },"json")
                    .fail(function() {
                        showError("При удалении файла, попробуйте, пожалуйста, еще раз чуть позже");
                        $("#image-id-"+id).removeClass("loading");
                    });
            }
        }
    }
    
    $('#photo-upload').fileupload({
        url: "/ajax/photo-upload.json", formData: {'id': $('#object-id').val(), 'imageOwner': objectData.imageOwner, 'object': objectData.image, 'securityCode': $("#security-code").val()},
        dataType: 'json',
        autoUpload: true,
        singleFileUploads: true,
        timeout: 30000,
        add: function(e, data) { if (data.autoUpload || (data.autoUpload !== false && $(this).fileupload('option', 'autoUpload'))) { data.submit(); } },
        done: function (e, data) {
            if (data.result.errors) {
                showError(data.result.errors);
            } else if (data.result.file && data.result.id) {
                if ($("#related-images").length == 1) {
                    $("#related-images").append(
                        '<div class="card text-white" style="background: url('+data.result.file+');" id="image-id-'+data.result.id+'">'+
                            '<div class="card-header"><ul class="list-inline mb-0 d-flex justify-content-between">' +
                                '<li class="list-inline-item" id="rotate-cw-'+data.result.id+'"><i class="fas fa-redo rotate-cw" title="Повернуть по часовой стрелке"></i></li>'+
                                '<li class="list-inline-item" id="rotate-acw-'+data.result.id+'"><i class="fas fa-undo rotate-acw" title="Повернуть против часовой стрелки"></i></li>'+
                                '<li class="list-inline-item" id="delete-'+data.result.id+'"><i class="fas fa-trash-alt delete" title="Удалить"></i></li>'+
                            '</ul></div></div>'
                    );
                    $("#image-id-"+data.result.id).show("blind", 800);
                    showImageUploaded("Файл "+(data.result.name ? "«"+data.result.name+"» " : "")+"успешно загружен", data.result.file);
                    $("#image-id-"+data.result.id+" .card-header li i").click(imageAction);
                } else if ($("form .my-gallery").length == 1) {
                    $("form .my-gallery").append(
                        '<a href="'+data.result.fileFull+'" itemprop="contentUrl" data-size="'+data.result.width+'x'+data.result.height+'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">'+
                            '<img src="'+data.result.file+'" itemprop="thumbnail" class="img-thumbnail mb-2 mr-1" alt="" />'+
                        '</a>'
                    );
                    showImageUploaded("Файл "+(data.result.name ? "«"+data.result.name+"» " : "")+"успешно загружен", data.result.file);
                    initPhotoSwipeFromDOM('.my-gallery');
                }
            }
        },
        fail: function(e, data) { showError("Не удалось отправить файл «"+data.files[0].name+"», попробуйте, пожалуйста, еще раз чуть позже"); }
    });
    
    $("#related-images .card-header li i").click(imageAction);
});