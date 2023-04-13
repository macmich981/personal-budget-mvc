$(document).ready(function() {
    $('#formSignup').validate({
            errorPlacement: function(error, element) {
            if (element.attr("name") == "username") {
                error.insertAfter(".nameErr");
                error.css('color', 'red');
            }
            else if (element.attr("name") == "email") {
                error.insertBefore(".emailErr");
                error.css('color', 'red');
            }
            else if (element.attr("name") == "password") {
                error.insertBefore(".passErr");
                error.css('color', 'red');
            }
            else if (element.attr("name") == "password_confirmation") {
                error.insertBefore(".confirmErr");
                error.css('color', 'red');
            }
        },
        rules: {
            name: 'required',
            email: {
                required: true,
                email: true,
                remote: '/account/validate-email'
            },
            password: {
                required: true,
                minlength: 6,
                validPassword: true
            },
            password_confirmation: {
                equalTo: '#inputPassword'
            }
        },
        messages: {
            email: {
                remote: 'Adres email jest już zajęty'
            }
        }
    });
});

$.validator.addMethod('validPassword',
    function(value, element, param) {
        if (value != '') {
            if (value.match(/.*[a-z]+.*/i) == null) {
                return false;
            }
            if (value.match(/.*\d+.*/) == null) {
                return false;
            }
        }
        return true;
    },
    'Musi zawierać co najmniej jedną literę i cyfrę'
);

jQuery.extend(jQuery.validator.messages, {
    required: "Pole wymagane.",
    remote: "Please fix this field.",
    email: "Proszę wpisać poprawny adres email.",
    url: "Please enter a valid URL.",
    date: "Please enter a valid date.",
    dateISO: "Please enter a valid date (ISO).",
    number: "Please enter a valid number.",
    digits: "Please enter only digits.",
    creditcard: "Please enter a valid credit card number.",
    equalTo: "Proszę wpisać ponownie.",
    accept: "Please enter a value with a valid extension.",
    maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
    minlength: jQuery.validator.format("Proszę wpisać co najmniej {0} znaków."),
    rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
    range: jQuery.validator.format("Please enter a value between {0} and {1}."),
    max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
    min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
});