let category = document.querySelectorAll('[name="category"]')[1];
let amount = document.querySelector('#inputExpense');

const getLimitForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR', e);
    }
}

category.addEventListener('change', async () => {
    let limit = await getLimitForCategory(category.value);

    if (limit != 0) {
        document.querySelector('#limit-amount').textContent = `Ustawiono limit w wysokości ${limit} zł miesięcznie`;
    } else {
        document.querySelector('#limit-amount').textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
    }
    document.querySelector('#limit-value').textContent = 'Wymagany wybór daty i kategorii';
    document.querySelector('#limit-balance').textContent = 'Wymagany wybór daty, kategorii i kwoty';
})

const getMonthlyExpensesForCategory = async (category, date) => {
    try {
        const res = await fetch(`../api/limit/${category}/${date}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR', e);
    }
}

$(document).ready(function () {
    $('#date').datepicker().on('changeDate', async () => {
        date = $('#date').val();
        let limit = await getLimitForCategory(category.value);

        if (limit != 0) {
            let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date);
            document.querySelector('#limit-value').textContent = `Wydałeś ${monthlyExpensesSum} zł w wybranym miesiącu dla tej kategprii`;
            document.querySelector('#limit-balance').textContent = (limit - monthlyExpensesSum).toFixed(2);
        }
    });
});

amount.addEventListener('input', async () => {
    let limit = await getLimitForCategory(category.value);
    let date = $('#date').val();
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date);
    expenseAmount = amount.value;

    if (limit != 0) {
        document.querySelector('#limit-balance').textContent = (limit - monthlyExpensesSum - expenseAmount).toFixed(2);
    }
})