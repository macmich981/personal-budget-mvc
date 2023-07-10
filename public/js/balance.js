$('#editExpenseModal').on('hidden.bs.modal', function(e){
    location.reload();          
 });
 
 $('#editIncomeModal').on('hidden.bs.modal', function(e){
     location.reload();          
 });
 
 // income data in edit income modal
 $(document).on('click', '#edit-income', function() {
     var income = $(this).data('income');
     $('.modal-body #incomeId').val(income.id);
     $('.modal-body #inputIncome').val(income.amount);
     $('.modal-body #date').val(income.date_of_income);
     $('.modal-body #selectIncomeCategory #incomeCategory').val(income.name).text(income.name);
     $("#selectIncomeCategory option").each(function() {
         $(this).siblings('[value="'+ this.value +'"]').remove();
     });
     $('.modal-body #comment').val(income.income_comment);
 });
 
 // income id for delete income modal
 $(document).on('click', '#delete-income', function() {
     var id = $(this).data('id');
     $('.modal-body #incomeId').val(id);
 });
 
 // expense id for delete expense modal
 $(document).on('click', '#delete-expense', function() {
     var id = $(this).data('id');
     $('.modal-body #expenseId').val(id);
 });
 
 // expense data in edit expense modal
 $(document).on('click', '#edit-expense', function() {
     var expense = $(this).data('expense');
     $('.modal-body #expenseId').val(expense.id);
     $('.modal-body #inputExpense').val(expense.amount);
     $('.modal-body #date').val(expense.date_of_expense);
     $('.modal-body #selectPaymentMethod #paymentMethod').val(expense.payment).text(expense.payment);
     $('.modal-body #selectExpenseCategory #expenseCategory').val(expense.name).text(expense.name);
     $("#selectPaymentMethod option, #selectExpenseCategory option").each(function() {
         $(this).siblings('[value="'+ this.value +'"]').remove();
     });
     $('.modal-body #comment').val(expense.expense_comment);
 });

//pie chart for expenses

var expenses = $('#for-expense-pie-chart').data('expenses');

function expenseLabels() {
    const labels = [];

    for (let i = 0; i < expenses.length; i++) {
        labels.push(expenses[i].name);
    }

    return labels;
}

function expenseAmounts() {
    const amounts = [];

    for (let i = 0; i < expenses.length; i++) {
        amounts.push(expenses[i].amount);
    }

    return amounts;
}

const ctxExpense = document.getElementById('expensesChart');

new Chart(ctxExpense, {
    type: 'pie',
    data: {
        labels: expenseLabels(),
        datasets: [{
            data: expenseAmounts(),
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(0, 128, 0)',
                'rgb(210, 105, 30)',
                'rgb(139, 0, 139)',
                'rgb(65, 105, 225)',
                'rgb(255, 127, 80)',
                'rgb(0, 128, 128)',
                'rgb(255, 0, 255)'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

//pie chart for incomes

var incomes = $('#for-income-pie-chart').data('incomes');

function incomeLabels() {
    const labels = [];

    for (let i = 0; i < incomes.length; i++) {
        labels.push(incomes[i].name);
    }

    return labels;
}

function incomeAmounts() {
    const amounts = [];

    for (let i = 0; i < incomes.length; i++) {
        amounts.push(incomes[i].amount);
    }

    return amounts;
}

const ctxIncome = document.getElementById('incomesChart');

new Chart(ctxIncome, {
    type: 'pie',
    data: {
        labels: incomeLabels(),
        datasets: [{
            data: incomeAmounts(),
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(0, 128, 0)',
                'rgb(210, 105, 30)',
                'rgb(139, 0, 139)',
                'rgb(65, 105, 225)',
                'rgb(255, 127, 80)',
                'rgb(0, 128, 128)',
                'rgb(255, 0, 255)'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});