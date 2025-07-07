<?php
require_once __DIR__ . '/../models/Modalite.php';

class ModaliteController {
    public static function getAll() {
        $modalites = Modalite::getAll();
        Flight::json($modalites);
    }
}
