$.fn.datepicker.dates['pl'] = {
    days: ["Niedziela", "Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota"],
    daysShort: ["Nd", "Pon", "Wt", "Śr", "Czw", "Pt", "Sob"],
    daysMin: ["Nd", "Pon", "Wt", "Śr", "Czw", "Pt", "Sob"],
    months: ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"],
    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    today: "Dziś",
    clear: "Clear",
    format: "yyyy-mm-dd"
    //titleFormat: "MM yyyy",
    //weekStart: 0
};

$(".date").datepicker({
    startDate: '2000-01-01',
    endDate: new Date(),
    language: 'pl',
    autoclose: true
});

$(document).ready(function() {
    $('#formAddExpense, #addExpenseModal').validate({
            errorPlacement: function(error, element) {
            if (element.attr("name") == "amount") {
                error.insertAfter(".amountErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "date") {
                error.insertBefore(".dateErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "payment") {
                error.insertBefore(".paymentErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "category") {
                error.insertBefore(".categoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            amount: {
                required: true,
                number: true,
                min: 0.01,
                max: 1000000.00
            },
            date: {
                required: true
            },
            payment: {
                required: true,
                remote: '/validator/validate-payment-method'
            },
            category: {
                required: true,
                remote: '/validator/validate-expense-category'
            }
        },
        messages: {
            amount: {
                number: 'Proszę wpisać kwotę do 2 miejsc po przecinku.',
                min: 'Proszę wpisać kwotę większą lub równą 0.01.',
                max: 'Kwota max. wynosi 1000000'
            },
            payment: {
                remote: 'Proszę wpisać istniejącą metodę płatności'
            },
            category: {
                remote: 'Proszę wpisać istniejącą kategorię wydatków'
            },
            date: {
                date: 'Proszę wpisać poprawną datę.'
            }
        }
    });

    $('#formAddIncome, #addIncomeModal').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "amount") {
                error.insertAfter(".amountErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "date") {
                error.insertBefore(".dateErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "category") {
                error.insertBefore(".categoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            amount: {
                required: true,
                number: true,
                min: 0.01,
                max: 1000000.00
            },
            date: {
                required: true
            },
            category: {
                required: true,
                remote: '/validator/validate-income-category'
            }
        },
        messages: {
            amount: {
                number: 'Proszę wpisać kwotę do 2 miejsc po przecinku.',
                min: 'Proszę wpisać kwotę większą lub równą 0.01.',
                max: 'Kwota max. wynosi 1000000'
            },
            category: {
                remote: 'Proszę wpisać istniejącą kategorię wydatków'
            },
            date: {
                date: 'Proszę wpisać poprawną datę.'
            }
        }
    });

    $('#period').change(function() {
        var opval = $(this).val(); 
        if (opval == "custom") { 
            $('#customPeriodModal').modal("show");
        }
    });

    $('#customPeriodForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "start-date") {
                error.insertAfter(".startDateErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "end-date") {
                error.insertBefore(".endDateErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            'start-date': {
                required: true
            },
            'end-date': {
                required: true,
                greaterThan: '#start-date'
            }
        },
        messages: {
            'start-date': {
                date: 'Proszę wpisać poprawną datę.'
            },
            'end-date': {
                date: 'Proszę wpisać poprawną datę.',
            }
        }
    });

    $('#changeEmailForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "email") {
                error.insertBefore(".emailErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            email: {
                required: true,
                email: true,
                remote: '/account/validate-email'
            }
        },
        messages: {
            email: {
                remote: 'Adres email jest już zajęty'
            }
        }
    })

    $('#changePasswordForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "old-password") {
                error.insertAfter(".oldPassErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "password") {
                error.insertBefore(".passErr");
                error.css('color', '#146c43');
            }
            else if (element.attr("name") == "password_confirmation") {
                error.insertBefore(".confirmErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            "old-password": {
                required: true,
                remote: '/validator/validate-old-password'
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
            "old-password": {
                remote: 'Wpisz swoje obecne hasło.'
            }
        }
    })

    $('#addPaymentForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "payment") {
                error.insertAfter(".paymentErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            payment: {
                required: true,
                remote: '/validator/payment-method-exist'
            }
        },
        messages: {
            payment: {
                remote: 'Metoda płatności już istnieje.'
            }
        }
    })

    $('#editPaymentForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "payment") {
                error.insertAfter(".paymentEditErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            payment: {
                required: true,
                remote: '/validator/payment-method-exist'
            }
        },
        messages: {
            payment: {
                remote: 'Metoda płatności już istnieje.'
            }
        }
    })

    $('#addIncomeCategoryForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "inputIncomeCategory") {
                error.insertAfter(".addIncomeCategoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            inputIncomeCategory: {
                required: true,
                remote: '/validator/income-category-exist'
            }
        },
        messages: {
            inputIncomeCategory: {
                remote: 'Kategoria przychodu już istnieje.'
            }
        }
    })

    $('#editIncomeCategoryForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "inputIncomeCategory") {
                error.insertAfter(".editIncomeCategoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            inputIncomeCategory: {
                required: true,
                remote: '/validator/income-category-exist'
            }
        },
        messages: {
            inputIncomeCategory: {
                remote: 'Kategoria przychodu już istnieje.'
            }
        }
    })

    $('#addExpenseCategoryForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "inputExpenseCategory") {
                error.insertAfter(".addExpenseCategoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            inputExpenseCategory: {
                required: true,
                remote: '/validator/expense-category-exist'
            }
        },
        messages: {
            inputExpenseCategory: {
                remote: 'Kategoria wydatku już istnieje.'
            }
        }
    })

    $('#editExpenseCategoryForm').validate({
        errorPlacement: function(error, element) {
            if (element.attr("name") == "inputExpenseCategory") {
                error.insertAfter(".editExpenseCategoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            inputExpenseCategory: {
                required: true,
                remote: '/validator/expense-category-exist'
            }
        },
        messages: {
            inputExpenseCategory: {
                remote: 'Kategoria wydatku już istnieje.'
            }
        }
    })

});

$.validator.methods.number = function(value, element, param) {
    return this.optional(element) || /^-?(?:\d+|\d+(?:\.\d{2})+)?(?:,\d+)?$/.test(value);
}

jQuery.validator.addMethod("greaterThan", 
    function(value, element, params) {
        return value >= $(params).val();
    },
'Data końcowa musi być większa od początkowej.');

