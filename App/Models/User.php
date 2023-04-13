<?php

namespace App\Models;

use PDO;

#[\AllowDynamicProperties]
class User extends \Core\Model {

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save() {
        $this->validate();

        if (empty($this->errors)) {
            $password = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (username, email, password)
                    VALUES (:username, :email, :password)';
            
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public function validate() {
        if ($this->username == '') {
            $this->errors[] = 'Imię jest wymagane';
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Niepoprawny adres email';
        }

        if (static::emailExists($this->email)) {
            $this->errors[] = 'Adres email jest już zajęty';
        }

        if ($this->password != $this->password_confirmation) {
            $this->errors[] = 'Hasła muszą być zgodne';
        }

        if (strlen($this->password) < 6) {
            $this->errors[] = 'Hasło musi składać się z co najmniej 6 znaków';
        }

        if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
            $this->errors[] = 'Hasło wymaga co najmniej jednej litery';
        }

        if (preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->errors[] = 'Hasło wymaga co najmniej jednej cyfry';
        }
    }

    public static function emailExists($email) {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch() !== false;
    }

}
