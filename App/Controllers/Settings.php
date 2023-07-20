<?php

namespace App\Controllers;

use \Core\View;
use App\Auth;
use App\Flash;
use App\Models\ExpenseModel;
use App\Models\IncomeModel;

class Settings extends Authenticated {

    public function indexAction() {
        View::renderTemplate('Settings/index.html', [
            'paymentMethods' => ExpenseModel::getPaymentMethodsCreatedByUser(),
            'incomeCategories' => IncomeModel::getIncomeCategoriesCreatedByUser(),
            'expenseCategories' => ExpenseModel::getExpenseCategoriesCreatedByUser(),
            'allExpenseCategories' => ExpenseModel::getExpenseCategories()
        ]);
    }

    public function changeEmailAction() {
        $user = Auth::getUser();
        $email = $_POST['email'];

        if ($user->changeEmail($email)) {
            FLASH::addMessage('Adres email został zmieniony.');
        } else {
            FLASH::addMessage('Adres email już zajęty lub błąd zapisu do bazy danych. Proszę sprówbować ponownie.', FLASH::WARNING);
        }
        $this->redirect('/settings/index');
    }

    public function changePasswordAction() {
        $user = Auth::getUser();
        $oldPassword = $_POST['old-password'];
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];


        if ($user->changePassword($oldPassword, $password, $password_confirmation)) {
            FLASH::addMessage('Hasło zostało zmienione.');
        } else {
            FLASH::addMessage('Niewłaście hasło lub błąd zapisu do bazy danych. Proszę sprówbować ponownie.', FLASH::WARNING);
        }
        $this->redirect('/settings/index');
    }

    public function addPaymentMethodAction() {
        $method = $_POST['payment'];

        if (!empty($method)) {
            if (!ExpenseModel::findPaymentMethod($method)) {
                ExpenseModel::savePaymentMethodAssignedToUser($method);
                FLASH::addMessage('Metoda płatności została dodana.');
            } else {
                FLASH::addMessage('Metoda płatności już istnieje.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }
    
    public function editPaymentMethodAction() {
        $method = $_POST['editPayment'];
        $newMethod = $_POST['payment'];

        if (!empty($method) && !empty($newMethod)) {
            if (ExpenseModel::updatePaymentAssignedToUser($method, $newMethod)) {
                FLASH::addMessage('Wybrana metoda płatności została zmieniona.');
            } else {
                FLASH::addMessage('Wybrana metoda płatności nie mogła zostać zmieniona.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function removePaymentMethodAction() {
        $method = $_POST['removePayment'];
        $option = $_POST['removeOptions'];

        if (!empty($method)) {
            if (ExpenseModel::deletePaymentAssignedToUser($method, $option)) {
                FLASH::addMessage('Wybrana metoda płatności została usunięta.');
            } else {
                FLASH::addMessage('Wybrana metoda nie mogła zostać usunięta.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function addIncomeCategoryAction() {
        $incomeCategory = $_POST['inputIncomeCategory'];

        if (!empty($incomeCategory)) {
            if (!IncomeModel::findCategory($incomeCategory)) {
                IncomeModel::saveCategoryAssignedToUser($incomeCategory);
                FLASH::addMessage('Kategoria przychodu została dodana.');
            } else {
                FLASH::addMessage('Kategoria przychodu już istnieje.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function editIncomeCategoryAction() {
        $category = $_POST['editIncomeCategory'];
        $newCategory = $_POST['inputIncomeCategory'];

        if (!empty($category) && !empty($newCategory)) {
            if (IncomeModel::updateIncomeCategoryAssignedToUser($category, $newCategory)) {
                FLASH::addMessage('Wybrana kategoria przychodu została zmieniona.');
            } else {
                FLASH::addMessage('Wybrana kategoria przychodu nie mogła zostać zmieniona.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function removeIncomeCategoryAction() {
        $category = $_POST['removeIncomeCategory'];
        $option = $_POST['removeIncomeCategoryOptions'];

        if (!empty($category)) {
            if (IncomeModel::deleteIncomeCategoryAssignedToUser($category, $option)) {
                FLASH::addMessage('Wybrana kategoria przychodu została usunięta.');
            } else {
                FLASH::addMessage('Wybrana kategoria przychodu nie mogła zostać usunięta.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function addExpenseCategoryAction() {
        $expenseCategory = $_POST['inputExpenseCategory'];

        if (!empty($expenseCategory)) {
            if (!ExpenseModel::findCategory($expenseCategory)) {
                ExpenseModel::saveCategoryAssignedToUser($expenseCategory);
                FLASH::addMessage('Kategoria wydatku została dodana.');
            } else {
                FLASH::addMessage('Kategoria wydatku już istnieje.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function editExpenseCategoryAction() {
        $category = $_POST['editExpenseCategory'];
        $newCategory = $_POST['inputExpenseCategory'];

        if (!empty($category) && !empty($newCategory)) {
            if (ExpenseModel::updateExpenseCategoryAssignedToUser($category, $newCategory)) {
                FLASH::addMessage('Wybrana kategoria wydatku została zmieniona.');
            } else {
                FLASH::addMessage('Wybrana kategoria wydatku nie mogła zostać zmieniona.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

    public function setLimitAction() {
        $category = $_POST['inputExpenseCategory'];
        $limit = $_POST['limitAmount'];

        if (!empty($category) && !empty($limit)) {
            if (ExpenseModel::setLimitForExpenseCategory($category, $limit)) {
                FLASH::addMessage('Limit został ustawiony.');
            } else {
                FLASH::addMessage('Wybrana kategoria nie istnieje.');
            }
        }
        $this->redirect('/settings/index');
    }

    public function getLimitAction() {
        $category = $this->route_params['category'];

        echo json_encode(ExpenseModel::getLimitForExpenseCategory($category), JSON_UNESCAPED_UNICODE);
    }

    public function removeExpenseCategoryAction() {
        $category = $_POST['removeExpenseCategory'];
        $option = $_POST['removeExpenseCategoryOptions'];

        if (!empty($category)) {
            if (ExpenseModel::deleteExpenseCategoryAssignedToUser($category, $option)) {
                FLASH::addMessage('Wybrana kategoria wydatku została usunięta.');
            } else {
                FLASH::addMessage('Wybrana kategoria wydatku nie mogła zostać usunięta.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }

}