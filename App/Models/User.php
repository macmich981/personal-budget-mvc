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
        $password = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (username, email, password)
                VALUES (:username, :email, :password)';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);

        $stmt->execute();
    }

}
