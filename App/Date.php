<?php

namespace App;

class Date {

    public static function getCurrentDate() {
        return Date('Y-m-d');
    }

    public static function validateDate($date, &$errors) {
        if ($date == '') {
            $errors[] = 'Data jest wymagana';
        } else {
            $created_date = date_create_from_format('Y-m-d', $date);

            if ($created_date === false) {
                $errors[] = 'Datę należy wpisać w formacie RRRR-MM-DD';
            } else {
                $date_errors = date_get_last_errors();
                $start_date = new \DateTime('2000-01-01');
                $end_date = date_create_from_format('Y-m-d', static::getCurrentDate());

                if (!empty($date_errors['warning_count'] > 0)) {
                    $errors[] = 'Niepoprawna data';
                } else if ($created_date < $start_date) {
                    $errors[] = 'Data przed 2000-01-01';
                } else if ($created_date > $end_date) {
                    $errors[] = 'Data po aktualnym dniu';
                }
            }
        }
    }

}