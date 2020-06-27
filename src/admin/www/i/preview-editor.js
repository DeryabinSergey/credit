$(function () {
    var previewTypesList = {0: '', 1: '1', 2: '2', 3: '3'};
    for(var i in previewTypesList) {
        var typeString = previewTypesList[i] ? previewTypesList[i] + '-' : '';

        $("#preview-"+typeString+"delete").on("click", (function(type, typeString) { return function() {
            $.post("/ajax/preview-delete.json", {'id': $('#object-id').val(), 'object': objectData.imageOwner, 'type': type ? type : objectData.previewType },
                function(result) {
                    if (result.success) {
                        $("#preview-"+typeString+"cont").parent().hide("blind", 800, function() {
                            $("#preview-"+typeString+"cont").css({"background-image": "none"});
                        });
                    } else {
                        if (result.errors) {
                            showError(result.errors);
                        } else {
                            showError("При удалении файла привью, попробуйте, пожалуйста, еще раз чуть позже");
                        }
                    }
                },"json")
                .fail(function() { showError("При удалении файла привью, попробуйте, пожалуйста, еще раз чуть позже"); });
        } })(previewTypesList[i], typeString));

        $("#preview-"+typeString+"upload").fileupload({
            url: "/ajax/preview-upload.json", formData: {'id': $('#object-id').val(), 'object': objectData.imageOwner, 'type': previewTypesList[i] ? previewTypesList[i] : objectData.previewType}, dataType: 'json', timeout: 30000,
            add: function (e, data) {
                if (data.autoUpload || (data.autoUpload !== false && $(this).fileupload('option', 'autoUpload'))) { data.submit(); }
            },
            done: (function(typeString) { return function(e, data) {
                if (data.result.errors) {
                    showError(data.result.errors);
                } else if (data.result.file) {
                    $("#preview-"+typeString+"cont").css({"background-image": "url("+data.result.file+")"});
                    $("#preview-"+typeString+"cont").parent().show("blind", 800);
                } else {
                    showError("При загрузке файла привью, попробуйте, пожалуйста, еще раз чуть позже");
                }
            } })(typeString),
            fail: function(e, data) { showError("При загрузке файла привью, попробуйте, пожалуйста, еще раз чуть позже"); }
        });
    }

});