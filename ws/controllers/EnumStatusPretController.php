<?php
require_once __DIR__ . '/../models/EnumStatusPret.php';

class EnumStatusPretController {
    public static function getAll() {
        $enums = EnumStatusPret::getAll();
        Flight::json($enums);
    }
}
