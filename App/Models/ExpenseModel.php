<?php

namespace App\Models;

use PDO;
use App\Date;

#[\AllowDynamicProperties]
class ExpenseModel extends \Core\Model {

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save() {
        $this->validate();

        if (empty($this->errors)) {
            $paymentMethodAssignedToUser = static::findPaymentMethodAssignedToUser($this->payment);
            $categoryAssignedToUser = static::findCategoryAssignedToUser($this->category);
            $db = static::getDB();

            if (!$paymentMethodAssignedToUser) {
                $paymentMethodId = static::savePaymentMethodAssignedToUser($this->payment);
            } else {
                $paymentMethodId = $paymentMethodAssignedToUser['id'];
            }

            if (!$categoryAssignedToUser) {
                $categoryId = static::saveCategoryAssignedToUser($this->category);
            } else {
                $categoryId = $categoryAssignedToUser['id'];
            }

            $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                    VALUES (:user_id, :expense_category_id, :payment_method_id, :amount, :date, :expense_comment)';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':expense_category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':payment_method_id', $paymentMethodId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':expense_comment', $this->comment, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public static function savePaymentMethodAssignedToUser($payment) {
        $db = static::getDB();
        $sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name)
                VALUES (:user_id, :name)';
                
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $payment, PDO::PARAM_STR);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function saveCategoryAssignedToUser($category) {
        $db = static::getDB();
        $sql = 'INSERT INTO expenses_category_assigned_to_users (user_id, name)
                VALUES (:user_id, :name)';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $category, PDO::PARAM_STR);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public function validate() {
        $this->errors = [];

        if ($this->amount == '') {
            $this->errors[] = 'Kwota jest wymagana';
        } else {
            $this->amount = str_replace(',', '.', $this->amount);

            if (!is_numeric($this->amount)) {
                $this->errors[] = 'Kwota musi być liczbą';
            } else {
                if ($this->amount <= 0) {
                    $this->errors[] = 'Kwota musi być większa od 0';
                } else if ($this->amount > 1000000) {
                    $this->errors[] = 'Kwota max. to 1000000';
                }
            }
        }

        Date::validateDate($this->date, $this->errors);

        if (!static::paymentMethodExists($this->payment)) {
            $this->errors[] = "Należy wybrać istniejący sposób płatności";
        }

        if (!static::categoryExists($this->category)) {
            $this->errors[] = 'Należy wybrać istniejącą kategorię wydatków';
        }

        if (strlen($this->comment) > 100) {
            $this->errors[] = 'Komentarz nie może być dłuższy niż 100 znaków';
        }
    }

    public static function paymentMethodExists($method) {
        $result = static::findPaymentMethod($method);

        if ($result) {
            return true;
        }
        return false;
    }

    public static function findPaymentMethod($method) {
        $sql = 'SELECT name FROM payment_methods_default 
                WHERE name = :method
                UNION
                SELECT name FROM payment_methods_assigned_to_users
                WHERE name = :method AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':method', $method, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function findPaymentMethodAssignedToUser($method) {
        $sql = 'SELECT id, name FROM payment_methods_assigned_to_users
                WHERE name = :method AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':method', $method, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getPaymentMethods() {
        $sql = 'SELECT name FROM payment_methods_default
                UNION
                SELECT name FROM payment_methods_assigned_to_users
                WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPaymentMethodsCreatedByUser() {
        $sql = 'SELECT name FROM payment_methods_assigned_to_users
                WHERE user_id = :user_id
                EXCEPT
                SELECT name FROM payment_methods_default';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function categoryExists($category) {
        $result = static::findCategory($category);        

        if ($result) {
            return true;
        }
        return false;
    }

    public static function findCategory($category) {
        $sql = 'SELECT name FROM expenses_category_default 
                WHERE name = :category
                UNION
                SELECT name FROM expenses_category_assigned_to_users
                WHERE name = :category AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findCategoryAssignedToUser($category) {
        $sql = 'SELECT id, name FROM expenses_category_assigned_to_users
                WHERE name = :category AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);        
    }

    public static function getExpenseCategories() {
        $sql = 'SELECT name FROM expenses_category_default
                UNION
                SELECT name FROM expenses_category_assigned_to_users
                WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getExpenseCategoriesCreatedByUser() {
        $sql = 'SELECT name FROM expenses_category_assigned_to_users
                WHERE user_id = :user_id
                EXCEPT
                SELECT name FROM expenses_category_default';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCustomDates($period, $custom_start_date = '0000-00-00', $custom_end_date = '0000-00-00') {
        $end_date = date_create_from_format('Y-m-d', Date::getCurrentDate());
        $start_date = date_create_from_format('Y-m-d', Date('Y-m-01'));

        switch ($period) {

            case 'previousMonth': {
                $end_date = date_create_from_format('Y-m-d', date('Y-m-t', strtotime('last day of last month')));
                $start_date = date_create_from_format('Y-m-d', date('Y-m-01', strtotime('first day of last month')));
                break;
            }

            case 'currentYear': {
                $start_date = date_create_from_format('Y-m-d', Date('Y-01-01'));
                break;
            }

            case 'custom': {
                if ($start_date != '0000-00-00' && $end_date != '0000-00-00') {
                    $end_date = date_create_from_format('Y-m-d', $custom_end_date);
                    $start_date = date_create_from_format('Y-m-d', $custom_start_date);
                }
                break;
            }
        }

        $dates = [];
        $dates['start_date'] = $start_date;
        $dates['end_date'] = $end_date;

        return $dates;
    }

    public static function getExpensesSumSortedByCategory($period, $custom_start_date = '0000-00-00', $custom_end_date = '0000-00-00') {
        $customDates = static::getCustomDates($period, $custom_start_date, $custom_end_date);
        $end_date = $customDates['end_date'];
        $start_date = $customDates['start_date'];

        $sql = 'SELECT expenses_category_assigned_to_users.name, SUM(expenses.amount) AS amount
                FROM expenses_category_assigned_to_users
                INNER JOIN expenses ON expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id
                WHERE expenses_category_assigned_to_users.user_id = :user_id AND expenses.user_id = :user_id AND expenses.date_of_expense BETWEEN :start_date AND :end_date
                GROUP BY expenses_category_assigned_to_users.name
                ORDER BY amount DESC';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $start_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $end_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDetailedExpenses($period, $custom_start_date = '0000-00-00', $custom_end_date = '0000-00-00') {
        $customDates = static::getCustomDates($period, $custom_start_date, $custom_end_date);
        $end_date = $customDates['end_date'];
        $start_date = $customDates['start_date'];

        $sql = 'SELECT expenses.id, expenses.amount, payment_methods_assigned_to_users.name AS payment, expenses_category_assigned_to_users.name, expenses.expense_comment, expenses.date_of_expense
                FROM expenses
                INNER JOIN expenses_category_assigned_to_users ON expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
                INNER JOIN payment_methods_assigned_to_users ON expenses.payment_method_assigned_to_user_id = payment_methods_assigned_to_users.id
                WHERE expenses_category_assigned_to_users.user_id = :user_id AND payment_methods_assigned_to_users.user_id = :user_id AND expenses.date_of_expense BETWEEN :start_date AND :end_date
                ORDER BY expenses.date_of_expense';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $start_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $end_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $this->validate();

        if (empty($this->errors)) {
            $paymentMethodAssignedToUser = static::findPaymentMethodAssignedToUser($this->payment);
            $categoryAssignedToUser = static::findCategoryAssignedToUser($this->category);
            $db = static::getDB();

            if (!$paymentMethodAssignedToUser) {
                $paymentMethodId = static::savePaymentMethodAssignedToUser($this->payment);
            } else {
                $paymentMethodId = $paymentMethodAssignedToUser['id'];
            }

            if (!$categoryAssignedToUser) {
                $categoryId = static::saveCategoryAssignedToUser($this->category);
            } else {
                $categoryId = $categoryAssignedToUser['id'];
            }

            $sql = 'UPDATE expenses
                    SET expense_category_assigned_to_user_id = :income_category_id,
                        payment_method_assigned_to_user_id = :payment_method_id,
                        amount = :amount,
                        date_of_expense = :date,
                        expense_comment = :income_comment
                    WHERE id = :id';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $this->expenseId, PDO::PARAM_INT);
            $stmt->bindValue(':payment_method_id', $paymentMethodId, PDO::PARAM_INT);
            $stmt->bindValue(':income_category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public static function delete($id) {
        $sql = 'DELETE FROM expenses
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function deletePaymentAssignedToUser($method, $option) {
        $methodAssignedToUser = static::findPaymentMethodAssignedToUser($method);
        $defaultPaymentMethod = static::findPaymentMethodAssignedToUser("Gotówka");

        if (!$defaultPaymentMethod) {
            $defaultPaymentMethodId = static::savePaymentMethodAssignedToUser("Gotówka");
        } else {
            $defaultPaymentMethodId = $defaultPaymentMethod['id'];
        }
        $db = static::getDB();

        if ($methodAssignedToUser) {
            if ($option == "1") {
                $sql = 'UPDATE expenses
                        SET payment_method_assigned_to_user_id = :deafult_id
                        WHERE payment_method_assigned_to_user_id = :method_id AND user_id = :user_id;
                        DELETE FROM payment_methods_assigned_to_users
                        WHERE id = :method_id;';

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':deafult_id', $defaultPaymentMethodId, PDO::PARAM_INT);
            } else if ($option == "2") {
                $sql = 'DELETE FROM expenses
                        WHERE user_id = :user_id AND payment_method_assigned_to_user_id = :method_id;
                        DELETE FROM payment_methods_assigned_to_users
                        WHERE id = :method_id;';

                $stmt = $db->prepare($sql);
            }
            $stmt->bindValue(':method_id', $methodAssignedToUser['id'], PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

    public static function updatePaymentAssignedToUser($method, $newMethod) {
        if (static::findPaymentMethodAssignedToUser($method)) {
            $sql = 'UPDATE payment_methods_assigned_to_users
                    SET name = :new_method
                    WHERE name = :method AND user_id = :user_id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':new_method', $newMethod, PDO::PARAM_STR);
            $stmt->bindValue(':method', $method, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

    public static function updateExpenseCategoryAssignedToUser($category, $newCategory) {
        if (static::findCategoryAssignedToUser($category)) {
            $sql = 'UPDATE expenses_category_assigned_to_users
                    SET name = :new_category
                    WHERE name = :category AND user_id = :user_id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':new_category', $newCategory, PDO::PARAM_STR);
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

    public static function deleteExpenseCategoryAssignedToUser($category, $option) {
        $categoryAssignedToUser = static::findCategoryAssignedToUser($category);
        $defaultCategory = static::findCategoryAssignedToUser("Inne");

        if (!$defaultCategory) {
            $defaultCategoryId = static::saveCategoryAssignedToUser("Inne");
        } else {
            $defaultCategoryId = $defaultCategory['id'];
        }
        $db = static::getDB();

        if ($categoryAssignedToUser) {
            if ($option == "1") {
                $sql = 'UPDATE expenses
                        SET expense_category_assigned_to_user_id = :deafult_id
                        WHERE expense_category_assigned_to_user_id = :category_id AND user_id = :user_id;
                        DELETE FROM expenses_category_assigned_to_users
                        WHERE id = :category_id;';

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':deafult_id', $defaultCategoryId, PDO::PARAM_INT);
            } else if ($option == "2") {
                $sql = 'DELETE FROM expenses
                        WHERE user_id = :user_id AND expense_category_assigned_to_user_id = :category_id;
                        DELETE FROM expenses_category_assigned_to_users
                        WHERE id = :category_id;';

                $stmt = $db->prepare($sql);
            }
            $stmt->bindValue(':category_id', $categoryAssignedToUser['id'], PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

}