<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Modalite.php';
require_once __DIR__ . '/../models/TypePret.php';

class PretService {
    public static function getAll() {
        return Pret::allWithRelations();
    }

    public static function getById($id) {
        return Pret::findWithRelations($id);
    }

    public static function create($data) {
        return Pret::create($data);
    }

    public static function update($id, $data) {
        return Pret::update($id, $data);
    }

    public static function delete($id) {
        return Pret::delete($id);
    }

    public static function getModalites() {
        return Modalite::getAll();
    }

    public static function getTypePrets() {
        return TypePret::getAll();
    }
}
