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

        if (!static::categoryExists($this->category)) {
            $this->errors[] = 'Należy wybrać kategorię wydatków';
        }
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

}