<?php
require_once __DIR__ . '/../models/StatusPret.php';

class StatusPretController {
    public static function getAll() {
        $status = StatusPret::getAll();
        Flight::json($status);
    }

    public static function getById($id) {
        $status = StatusPret::getById($id);
        Flight::json($status);
    }

    public static function create() {
        $data = Flight::request()->data;
        $id = StatusPret::create($data);
        Flight::json(['message' => 'Status prêt ajouté', 'id' => $id]);
    }

    public static function update($id) {
        $data = Flight::request()->data;
        StatusPret::update($id, $data);
        Flight::json(['message' => 'Status prêt modifié']);
    }

    public static function delete($id) {
        StatusPret::delete($id);
        Flight::json(['message' => 'Status prêt supprimé']);
    }
}
