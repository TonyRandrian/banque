<?php
require_once __DIR__ . '/../services/PretService.php';

class PretController {
    public static function getAll() {
        echo json_encode(PretService::getAll());
    }

    public static function getById($id) {
        echo json_encode(PretService::getById($id));
    }

    public static function create() {
        $data = Flight::request()->data->getData();
        echo json_encode(PretService::create($data));
    }

    public static function update($id) {
        $data = Flight::request()->data->getData();
        echo json_encode(PretService::update($id, $data));
    }

    public static function delete($id) {
        echo json_encode(PretService::delete($id));
    }

    public static function getModalites() {
        echo json_encode(PretService::getModalites());
    }

    public static function getTypePrets() {
        echo json_encode(PretService::getTypePrets());
    }
}
