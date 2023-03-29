/*document.addEventListener('DOMContentLoaded', function() {*/

/*var myInput = document.getElementsByClassName("passwordstrength");*/
var myInput = document.getElementById("password");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");
var rExpression = document.getElementById("regularExpression");


myInput.onkeyup = function() {

    // Validate lowercase letters
    var lowerCaseLetters = /[a-z]/g;
    if (myInput.value.match(lowerCaseLetters)) {
        letter.classList.remove("invalid");
        letter.classList.add("valid");
    } else {
        letter.classList.remove("valid");
        letter.classList.add("invalid");
    }

    // Validate capital letters
    var upperCaseLetters = /[A-Z]/g;
    if (myInput.value.match(upperCaseLetters)) {
        capital.classList.remove("invalid");
        capital.classList.add("valid");
    } else {
        capital.classList.remove("valid");
        capital.classList.add("invalid");
    }

    // Validate numbers
    var numbers = /[0-9]/g;
    if (myInput.value.match(numbers)) {
        number.classList.remove("invalid");
        number.classList.add("valid");
    } else {
        number.classList.remove("valid");
        number.classList.add("invalid");
    }

    // Validate length
    var lengths = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{6,16}$/;
    if (myInput.value.match(lengths)) {
        length.classList.remove("invalid");
        length.classList.add("valid");
    } else {
        length.classList.remove("valid");
        length.classList.add("invalid");
    }

    //   Special Character Validation
    var regularExpression = "!@#$%^&*()+=-[]\\\';,./{}|\":<>?";

    var regex = /^[A-Za-z0-9 ]+$/

    //Validate TextBox value against the Regex.
    if (myInput.value != '') {
        var isValid = regex.test(myInput.value);
        if (!isValid) {
            rExpression.classList.remove("invalid");
            rExpression.classList.add("valid");
        } else {
            rExpression.classList.remove("valid");
            rExpression.classList.add("invalid");
        }
    } else {
        rExpression.classList.remove("valid");
        rExpression.classList.add("invalid");
    }
}
/*}, false);*/

define([
    "jquery",
    "drlregisterjs",
    "jquery/ui"
], function($) {
    "use strict";
    $('body').on('click', '.show-hide', function() {

        if ($(this).html() == '<i class="fa fa-eye" aria-hidden="true"></i>') {
            $(this).html('<i class="fa fa-eye-slash" aria-hidden="true"></i>');
            $("#password").attr("type", "text");
        } else {
            $(this).html('<i class="fa fa-eye" aria-hidden="true"></i>');
            $("#password").attr("type", "password");
        }
    });

});
