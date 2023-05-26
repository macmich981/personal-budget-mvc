<?php

namespace App\Models;

use PDO;
use App\Date;

#[\AllowDynamicProperties]
class IncomeModel extends \Core\Model {

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save() {
        $this->validate();

        if (empty($this->errors)) {
            $categoryAssignedToUser = static::findCategoryAssignedToUser($this->category);
            $db = static::getDB();

            if (!$categoryAssignedToUser) {
                $categoryId = static::saveCategoryAssignedToUser($this->category);
            } else {
                $categoryId = $categoryAssignedToUser['id'];
            }

            $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
                    VALUES (:user_id, :income_category_id, :amount, :date, :income_comment)';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':income_category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public static function saveCategoryAssignedToUser($category) {
        $db = static::getDB();
        $sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name)
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

        if (!static::categoryExists($this->category)) {
            $this->errors[] = 'Należy wybrać istniejącą kategorię przychodu';
        }

        if (strlen($this->comment) > 100) {
            $this->errors[] = 'Komentarz nie może być dłuższy niż 100 znaków';
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
        $sql = 'SELECT name FROM incomes_category_default 
                WHERE name = :category
                UNION
                SELECT name FROM incomes_category_assigned_to_users
                WHERE name = :category AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findCategoryAssignedToUser($category) {
        $sql = 'SELECT id, name FROM incomes_category_assigned_to_users
                WHERE name = :category AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);        
    }

    public static function getIncomeCategories() {
        $sql = 'SELECT name FROM incomes_category_default
                UNION
                SELECT name FROM incomes_category_assigned_to_users
                WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getIncomeCategoriesCreatedByUser() {
        $sql = 'SELECT name FROM incomes_category_assigned_to_users
                WHERE user_id = :user_id
                EXCEPT
                SELECT name FROM incomes_category_default';

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
                $end_date = date_create_from_format('Y-m-d', date('Y-m-t', strtotime('-1 month')));
                $start_date = date_create_from_format('Y-m-d', date('Y-m-01', strtotime('-1 month')));
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

    public static function getIncomesSumSortedByCategory($period, $custom_start_date = '0000-00-00', $custom_end_date = '0000-00-00') {
        $customDates = static::getCustomDates($period, $custom_start_date, $custom_end_date);
        $end_date = $customDates['end_date'];
        $start_date = $customDates['start_date'];

        $sql = 'SELECT incomes_category_assigned_to_users.name, SUM(incomes.amount) AS amount
                FROM incomes_category_assigned_to_users
                INNER JOIN incomes ON incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id
                WHERE incomes_category_assigned_to_users.user_id = :user_id AND incomes.user_id = :user_id AND incomes.date_of_income BETWEEN :start_date AND :end_date
                GROUP BY incomes_category_assigned_to_users.name
                ORDER BY amount DESC';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $start_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $end_date->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDetailedIncomes($period, $custom_start_date = '0000-00-00', $custom_end_date = '0000-00-00') {
        $customDates = static::getCustomDates($period, $custom_start_date, $custom_end_date);
        $end_date = $customDates['end_date'];
        $start_date = $customDates['start_date'];

        $sql = 'SELECT incomes.id, incomes.amount, incomes_category_assigned_to_users.name, incomes.income_comment, incomes.date_of_income
                FROM incomes
                INNER JOIN incomes_category_assigned_to_users ON incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
                WHERE incomes_category_assigned_to_users.user_id = :user_id AND incomes.date_of_income BETWEEN :start_date AND :end_date
                ORDER BY incomes.date_of_income';

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
            $categoryAssignedToUser = static::findCategoryAssignedToUser($this->category);
            $db = static::getDB();

            if (!$categoryAssignedToUser) {
                $categoryId = static::saveCategoryAssignedToUser($this->category);
            } else {
                $categoryId = $categoryAssignedToUser['id'];
            }

            $sql = 'UPDATE incomes
                    SET income_category_assigned_to_user_id = :income_category_id,
                        amount = :amount,
                        date_of_income = :date,
                        income_comment = :income_comment
                    WHERE id = :id';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $this->incomeId, PDO::PARAM_INT);
            $stmt->bindValue(':income_category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public static function delete($id) {
        $sql = 'DELETE FROM incomes
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function updateIncomeCategoryAssignedToUser($category, $newCategory) {
        if (static::findCategoryAssignedToUser($category)) {
            $sql = 'UPDATE incomes_category_assigned_to_users
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

    public static function deleteIncomeCategoryAssignedToUser($category, $option) {
        $categoryAssignedToUser = static::findCategoryAssignedToUser($category);
        $defaultCategoryId = static::findCategoryAssignedToUser("Another")['id'];

        if (!$defaultCategoryId) {
            $defaultCategoryId = static::saveCategoryAssignedToUser("Another");
        }
        $db = static::getDB();

        if ($categoryAssignedToUser) {
            if ($option == "1") {
                $sql = 'UPDATE incomes
                        SET income_category_assigned_to_user_id = :deafult_id
                        WHERE income_category_assigned_to_user_id = :category_id AND user_id = :user_id;
                        DELETE FROM incomes_category_assigned_to_users
                        WHERE id = :category_id;';

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':deafult_id', $defaultCategoryId, PDO::PARAM_INT);
            } else if ($option == "2") {
                $sql = 'DELETE FROM incomes
                        WHERE user_id = :user_id AND income_category_assigned_to_user_id = :category_id;
                        DELETE FROM incomes_category_assigned_to_users
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