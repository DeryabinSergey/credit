$(document).ready(function(){
    for (var i in selectList) {
        if ($("#checkbox-group-"+i).length) {
            $("#checkbox-group-"+i).on("change", function(el) { return function() {
                for(var k in selectList[el]) {
                    $("#checkbox-right-"+selectList[el][k]).prop('checked', $("#checkbox-group-"+el).prop('checked'));
                }
            } }(i));          
            var checked = true;
            for (var j in selectList[i]) {
                if (!$("#checkbox-right-"+selectList[i][j]).prop('checked')) {
                    checked = false;
                    break;
                }
            }
            $("#checkbox-group-"+i).prop('checked', checked);
        }
    }
            
    for (var i in childList) {
        $("#checkbox-right-"+i).on("change", function(el) { return function() {
            var parent = childList[el];
            var checked = true;
            for (var j in selectList[parent]) {
                if (!$("#checkbox-right-"+selectList[parent][j]).prop('checked')) {
                    checked = false;
                    break;
                }
            }
            $("#checkbox-group-"+parent).prop('checked', checked);
        } }(i));                
    }
});