
$(document).ready(function() {


    $("#delete-img").click(function (event) {
        var text;
        var r = confirm("Seguro que quiere borrar la imagen?");

        if (r == true) {
        } else {
            event.preventDefault();
        }

    });

});




