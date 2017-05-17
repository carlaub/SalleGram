
$(document).ready(function() {


    $(document).on('click', "#delete-img", function (event) {
        var text;
        var r = confirm("Seguro que quiere borrar la imagen?");

        if (r == true);
        else event.preventDefault();
    });
});




