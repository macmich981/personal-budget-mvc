<?php

namespace App\Models;

use PDO;
use App\Token;
use App\Mail;
use \Core\View;

#[\AllowDynamicProperties]
class User extends \Core\Model {

    public function __construct($data = []) {
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
        return static::findByEmail($email) !== false;
    }

    public static function findByEmail($email) {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function authenticate($email, $password) {
        $user = static::findByEmail($email);

        if ($user) {
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }
        return false;
    }

    public static function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetch();
    }
    
    public static function sendPasswordReset($email) {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
                return true;
            }
        }
        return false;
    }

    protected function startPasswordReset() {
        $token = new Token();
        $hashed_token = $token->getHash();
        $expiry_timestamp = time() + 60 * 60 * 2;
        $this->password_reset_token = $token->getValue();

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';
            
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);       
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail() {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;
        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this->email, 'Password reset', $text, $html);
    }

}
