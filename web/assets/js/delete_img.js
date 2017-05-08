
$(document).ready(function() {


    $("#delete-img").click(function (event) {
        var text;
        var r = confirm("Seguro que quiere borrar la imagen?");
        bootbox.confirm("Are you sure?", function(result){ /* your callback code */ })

        if (r == true) {
        } else {
            event.preventDefault();
        }

    });

});




