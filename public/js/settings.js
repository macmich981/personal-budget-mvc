$(document).ready(function() {
    $('#limitForm').validate({
            errorPlacement: function(error, element) {
            if (element.attr("name") == "limitAmount") {
                error.insertAfter(".limitErr");
                error.css('color', '#146c43');
            } else if (element.attr("name") == "inputExpenseCategory") {
                error.insertAfter(".limitCategoryErr");
                error.css('color', '#146c43');
            }
        },
        rules: {
            limitAmount: {
                required: true,
                number: true,
                min: 0.01,
                max: 1000000.00
            },
            inputExpenseCategory: {
                required: true
            }
        },
        messages: {
            limitAmount: {
                number: 'Proszę wpisać kwotę do 2 miejsc po przecinku.',
                min: 'Proszę wpisać kwotę większą lub równą 0.01.',
                max: 'Kwota max. wynosi 1000000'
            },
            inputExpenseCategory: {
                remote: 'Proszę wpisać istniejącą kategorię wydatków'
            }
        }
    })
});