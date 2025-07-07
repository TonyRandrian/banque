<?php
require_once __DIR__ . '/../services/PretService.php';

class PretController {
    public static function getAll() {
        try {
            echo json_encode(PretService::getAll());
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            echo json_encode(PretService::getById($id));
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function create() {
        try {
            $data = Flight::request()->data->getData();
            echo json_encode(PretService::create($data));
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function update($id) {
        try {
            $data = Flight::request()->data;
            if (is_object($data)) {
                $data = (array)$data;
            }
            echo json_encode(PretService::update($id, $data));
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }                                        

    public static function delete($id) {
        try {
            echo json_encode(PretService::delete($id));
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getModalites() {
        try {
            echo json_encode(PretService::getModalites());
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getTypePrets() {
        try {
            echo json_encode(PretService::getTypePrets());
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
