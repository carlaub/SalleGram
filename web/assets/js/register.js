
$(document).ready(function() {


    $("#registerUserForm").submit(function (event) {

        var username = $("#username").val();
        var email = $("#mail").val();
        var date = $("#date").val();
        var password = $("#password").val();
        var password2 = $("#confirm-password").val();
        var imgPath = $("#imgage-path").val();

        if(validateName(username) === true && validateEmail(email) === true && validateDate(date) === true
            && validatePassword(password, password2) === true && validateImagePath(imgPath) === true) {
            //alert("ok!");

        }else{
            event.preventDefault();
            //alert("error validacio");

        }

    });

    function validateName(userName) {
        //NO cumple longitud minima
        var element = document.getElementById("username");

        if(userName.length > 21 || userName === null || userName.length < 1) {//menys de 20 caracters
            element.placeholder = "Introduce username";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }
        else if(!userName.match(/^[A-Za-z0-9]+$/)) {//alfanumeric?
            element.placeholder = "El nombre tiene que ser alfanumerico";
            element.value = "";
            element.className = "form-control-red";
            return false;
        }
        element.className = "form-control-green";
        return true;
    }

    function validateEmail(email) {

        var element = document.getElementById("mail");

        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if(!email.match(re) || email.empty()) {
            element.className = "form-control-red";
            element.placeholder = "Mail incorrecto";
            element.value = "";
            return false;
        }else {
            element.className = "form-control-green";
            return true;
        }
    }



    function validateDate(date) {
        var element = document.getElementById("date");

        //TODO SOLO MIRO QUE NO ESTE VACIO hay que hacer:  (no pot ser una data futura) i ha de seguir el format ISO 8601 (lo segon sera per php quan guardem a la bbdd).
        if (date.length === 0 || date.empty()) {
            element.placeholder = "Introduce una fecha valida dd/mm/yyyy";
            element.className = "form-control-red";
            return false;
        }
        else {
            element.className = "form-control-green";
            return true;
        }

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
        //TODO creo que no hay que hacer nada, si no hay nada es una por defecto
        //TODO hay que mostrar la imagen que se ha escogido
        /*if(imgPath.empty()){
         element.placeholder = "Introduce el path de una imagen";
         element.value = "";
         element.className = "form-control-red";
         return false;
         }*/

        element.className = "form-control-green";
        return true;
    }

});




