const category = document.querySelectorAll('[name="category"]')[1];
const amount = document.querySelector('#inputExpense');
const date = document.querySelector('#date');
const limitBalance = document.querySelector('#limit-balance');
const limitAmount = document.querySelector('#limit-amount');
const limitValue = document.querySelector('#limit-value');

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
    if (category.value != 'Wybierz rodzaj wydatku...') {
        let limit = await getLimitForCategory(category.value);
        let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

        if (!isNaN(limit) && limit != 0) {
            let balance = (limit - monthlyExpensesSum - amount.value).toFixed(2);
            changeBalanceColor(balance, limitBalance);
            limitBalance.textContent = `${balance} zł`;
        }
    }
})

category.addEventListener('change', async () => {
    let limit = await getLimitForCategory(category.value);
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

    if (!isNaN(limit) && limit != 0) {
        let balance = (limit - monthlyExpensesSum - amount.value).toFixed(2);
        limitAmount.textContent = `Ustawiono limit w wysokości ${limit} zł miesięcznie`;
        changeBalanceColor(balance, limitBalance);
        limitBalance.textContent = `${balance} zł`;
    } else {
        limitAmount.textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
        changeBalanceColor(0, limitBalance, true);
        limitBalance.textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
    }
    limitValue.textContent = `Wydałeś ${monthlyExpensesSum} zł w wybranym miesiącu dla tej kategorii`;
    
})

// how to tranform code below to pure javascript?

$('#date').datepicker().on('changeDate', async () => {
    let limit = await getLimitForCategory(category.value);
    let monthlyExpensesSum = await getMonthlyExpensesForCategory(category.value, date.value);

    if (category.value != 'Wybierz rodzaj wydatku...') {
        limitValue.textContent = `Wydałeś ${monthlyExpensesSum} zł w wybranym miesiącu dla tej kategorii`;
    }
    
    if (!isNaN(limit) && limit != 0) {
        let balance = (limit - monthlyExpensesSum - amount.value).toFixed(2);
        changeBalanceColor(balance, limitBalance);
        limitBalance.textContent = `${balance} zł`;
    } else {
        changeBalanceColor(balance, limitBalance, true);
        limitBalance.textContent = 'Nie ustawiono limitu wydatków dla tej kategorii';
    }
});

function changeBalanceColor(balance, selector, defaultColor = false) {
    if (defaultColor) {
        selector.classList.remove('red-text');
        selector.classList.remove('green-text');
        selector.classList.add('black-text');
        return;
    }

    if (balance >= 0) {
        selector.classList.add('green-text');
        selector.classList.remove('red-text');
        selector.classList.remove('black-text');
    } else {
        selector.classList.add('red-text');
        selector.classList.remove('green-text');
        selector.classList.remove('black-text');
    }
}