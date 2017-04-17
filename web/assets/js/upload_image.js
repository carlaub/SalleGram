
$(document).ready(function() {


    $("#uploadImageForm").submit(function (event) {

        var title = $("#img-title").val();
        var img = $("#img-selected").val();



        if(validateTitle(title) & validateImg(img)) {
            //alert("ok!");

        }else{
            event.preventDefault();
            //alert("error validacio");
        }

    });

    function validateTitle(title) {

        var element = document.getElementById("img-title");

        if(title === '') {
            element.placeholder = "No has introducido ningún título";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }

        element.className = "form-control";
        return true;

    }

    function validateImg(img) {
        var element = document.getElementById("img-selected");
        if (img  === ''){
            element.className = "form-control-red";
            return false;
        }else{
            element.className = "form-control";
            return true;
        }
    }
});




