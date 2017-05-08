
$(document).ready(function() {


    $("#editUserForm").submit(function (event) {

        var username = $("#username").val();
        var date = $("#date").val();
        var password = $("#password").val();
        var password2 = $("#confirm-password").val();
        var imgPath = $("#imgage-path").val();

        if(validateDate(date) & validateName(username)  & validatePassword(password, password2) & validateImagePath(imgPath)) {
            //alert("ok!");

        }else{
            event.preventDefault();
            $('#btn-register').popover('show');
            //alert("error validacio");
        }

    });

    function validateName(userName) {

        var element = document.getElementById("username");

        var regEx = /[a-zA-Z0-9]+$/;

        if(userName.length <= 0) {
            element.placeholder = "No has introducido ningún nombre";
            element.value = "";
            element.className = "form-control-red";
            return false;
        } else if (userName.length > 20){
            element.placeholder = "El nombre demasiado largo";
            element.value = "";
            element.className = "form-control-red";
            return false;
        } else if (!regEx.test(userName)) {
            element.placeholder = "El nombre ha de ser alfanumerico";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }

        element.className = "form-control-green";
        return true;

    }


    function checkdate (date) {

        var act = new Date();
        var d = new Date (date);
        return d.getTime() <= act.getTime();
    }


    function validateDate(date) {
        var element = document.getElementById("date");
        var dateFragment = date.split("-");

        if (date.length === 0 ) {
            element.className = "form-control-red";
            return false;
        }

        if(!checkdate(date)) {
            element.className = "form-control-red";
            return false;
        }

        element.className = "form-control-green";
        return true;
    }



    function validatePassword(password, password2) {

        var element = document.getElementById("password");
        //NO tiene minimo
        if(password.length < 6 || password.length > 12){
            element.placeholder = "La contrasenya tiene que tener entre 6 i 12 caracteres";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }
        // SI longitud, NO VALIDO numeros y letras
        else if(!password.match(/(?=\w*\d)(?=\w*[A-Z])(?=\w*[a-z])\S{6,12}/)){
            element.placeholder = "La contraseña tiene que tener mayusculas, minusculas y numeros";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }
        else if(password != password2){
            element.className = "form-control-green";
            element = document.getElementById("confirm-password");
            element.placeholder = "Las contraseñas no coinciden";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }
        element = document.getElementById("password");
        element.className = "form-control-green";
        element = document.getElementById("confirm-password");
        element.className = "form-control-green";
        return true;
    }

    function validateImagePath(imgPath) {
        var element = document.getElementById("img-path");
        /*if(imgPath.empty()){
         element.placeholder = "Introduce el path de una imagen";
         element.value = "";
         element.className = "form-control-red";
         return false;
         }*/

        //element.className = "form-control-green";
        return true;
    }

});




