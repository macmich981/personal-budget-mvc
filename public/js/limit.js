const category = document.querySelectorAll('[name="category"]')[1];
const amount = document.querySelector('#inputExpense');
const date = document.querySelector('#date');

const getLimitForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR', e);
    }
}

const getMonthlyExpensesForCategory = async (category, date) => {
    try {
        const res = await fetch(`../api/limit/${category}/${date}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR', e);
    }
}

amount.addEventListener('input', async () => {
    let limit = await getLimitForCategory(category.value);
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

    if (limit != 0) {
        document.querySelector('#limit-balance').textContent = (limit - monthlyExpensesSum - amount.value).toFixed(2);
    }
})

category.addEventListener('change', async () => {
    let limit = await getLimitForCategory(category.value);
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

    if (limit != 0) {
        document.querySelector('#limit-amount').textContent = `Ustawiono limit w wysokości ${limit} zł miesięcznie`;
        document.querySelector('#limit-balance').textContent = (limit - monthlyExpensesSum).toFixed(2);
    } else {
        document.querySelector('#limit-amount').textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
        document.querySelector('#limit-balance').textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
    }
    document.querySelector('#limit-value').textContent = `Wydałeś ${monthlyExpensesSum} zł w wybranym miesiącu dla tej kategorii`;
    
})

// how to tranform code below to pure javascript?


$('#date').datepicker().on('changeDate', async () => {
    let limit = await getLimitForCategory(category.value);
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

    if (category.value != 'Wybierz rodzaj wydatku...') {
        document.querySelector('#limit-value').textContent = `Wydałeś ${monthlyExpensesSum} zł w wybranym miesiącu dla tej kategorii`;
    }
    
    if (!isNaN(limit) && limit != 0) {
        document.querySelector('#limit-balance').textContent = (limit - monthlyExpensesSum).toFixed(2);
    }
});
