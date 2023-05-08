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
            $paymentMethodId = null;
            $paymentMethodAssignedToUser = static::findPaymentMethodAssignedToUser($this->payment);
            $categoryId = null;
            $categoryAssignedToUser = static::findCategoryAssignedToUser($this->category);
            $db = static::getDB();

            if (!$paymentMethodAssignedToUser) {
                $sql = 'INSERT INTO payment_methods_assigned_to_users (user_id, name)
                        VALUES (:user_id, :name)';
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':name', $this->payment, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    return false;
                }

                $paymentMethodId = $db->lastInsertId();
            } else {
                $paymentMethodId = $paymentMethodAssignedToUser['id'];
            }

            if (!$categoryAssignedToUser) {
                $sql = 'INSERT INTO expenses_category_assigned_to_users (user_id, name)
                        VALUES (:user_id, :name)';

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':name', $this->category, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    return false;
                }

                $categoryId = $db->lastInsertId();
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
                } else if ($this->amount > 1000000000) {
                    $this->errors[] = 'Kwota max. to 1000000000';
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

    public static function getExpensesSumSortedByCategory($period) {
        $end_date = date_create_from_format('Y-m-d', Date::getCurrentDate());
        $start_date = date_create_from_format('Y-m-d', Date('Y-m-01'));

        switch ($period) {
            case 'previousMonth': {
                $end_date = date_create_from_format('Y-m-d', date('Y-m-t', strtotime('-1 month')));
                $start_date = date_create_from_format('Y-m-d', date('Y-m-01', strtotime('-1 month')));
                break;
            }

            case 'currentYear': {
                $start_date = date_create_from_format('Y-m-d', Date('Y-01-01'));
                break;
            }
        }

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

}