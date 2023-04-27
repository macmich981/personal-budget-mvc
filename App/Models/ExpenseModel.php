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
        }
    }

    public function validate() {
        if ($this->amount == '') {
            $this->errors[] = 'Kwota jest wymagana';
        } else {
            $this->amount = str_replace(',', '.', $this->amount);

            if (!is_numeric($this->amount)) {
                $this->errors[] = 'Kwota musi być liczbą';
            } else {
                if ($this->amount < 0) {
                    $this->errors[] = 'Kwota musi być większa od 0';
                }
            }
        }

        if ($this->date == '') {
            $this->errors[] = 'Data jest wymagana';
        } else {
            $date = date_create_from_format('Y-m-d', $this->date);

            if ($date === false) {
                $this->errors[] = 'Datę należy wpisać w formacie RRRR-MM-DD';
            } else {
                $date_errors = date_get_last_errors();
                $start_date = new \DateTime('2000-01-01');
                $end_date = date_create_from_format('Y-m-d', Date::getCurrentDate());

                if (!empty($date_errors)) {
                    $this->errors[] = 'Niepoprawna data';
                } else if ($date < $start_date) {
                    $this->errors[] = 'Data przed 2000-01-01';
                } else if ($date > $end_date) {
                    $this->errors[] = 'Data po aktualnym dniu';
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

    public static function paymentMethodExists($method, $assigned_to_user = false) {
        if ($assigned_to_user) {
            $result = static::findPaymentMethodAssignedToUser($method);
        } else {
            $result = static::findPaymentMethod($method);
        }

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
        $sql = 'SELECT name FROM payment_methods_assigned_to_users
                WHERE name = :method AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':method', $method, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
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

    public static function categoryExists($category, $assigned_to_user = false) {
        if ($assigned_to_user) {
            $result = static::findCategoryAssignedToUser($category);
        } else {
            $result = static::findCategory($category);
        }
        

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
        $sql = 'SELECT name FROM expenses_category_assigned_to_users
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
}