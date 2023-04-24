<?php

namespace App\Models;

use PDO;

#[\AllowDynamicProperties]
class ExpenseModel extends \Core\Model {

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save() {
        $this->validate();
    }

    public function validate() {
        if ($this->amount == '') {
            $this->errors[] = 'Kwota jest wymagana';
        } else {
            $this->amount = str_replace(',', '.', $this->amount);

            if (!is_numeric($this->amount)) {
                $this->errors[] = 'Kwota musi być liczbą';
            }
        }

        if ($this->date == '') {
            $this->errors[] = 'Data jest wymagana';
        } else {
            $date = date_create_from_format('Y-m-d', $this->date);

            if ($date === false) {
                $this->errors[] = 'Niepoprawny format daty';
            } else {
                $date_errors = date_get_last_errors();
                $start_date = new \DateTime('2000-01-01');

                if (!empty($date_errors) || $date < $start_date) {
                    $this->errors[] = 'Niepoprawna data lub data przed 2000-01-01';
                }
            }
        }

        if (!static::paymentMethodExists($this->payment)) {
            $this->errors[] = "Należy wybrać istniejący sposób płatności";
        }

        if (!static::categoryExists($this->category)) {
            $this->errors[] = 'Należy wybrać istniejącą kategorię wydatków';
        }
    }

    public static function paymentMethodExists($method) {
        $result = static::findpaymentMethod($method);

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
                WHERE name = :method';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':method', $category, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
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
                WHERE name = :category';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
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
}