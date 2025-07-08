<?php
require_once __DIR__ . '/../models/CompteClient.php';

class CompteClientController {
    public static function getAll() {
        try {
            $comptes = CompteClient::getAll();
            Flight::json($comptes);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getById($id) {
        try {
            $compte = CompteClient::getById($id);
            Flight::json($compte);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function create() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            if (empty($data['numero']) || empty($data['date_creation']) || empty($data['client_id'])) {
                Flight::json(['error' => 'Tous les champs sont requis'], 400);
                return;
            }
            $id = CompteClient::create($data);
            Flight::json(['message' => 'Compte client ajouté', 'id' => $id]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function update($id) {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            if (empty($data['numero']) || empty($data['date_creation']) || empty($data['client_id'])) {
                Flight::json(['error' => 'Tous les champs sont requis'], 400);
                return;
            }
            CompteClient::update($id, $data);
            Flight::json(['message' => 'Compte client modifié']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function delete($id) {
        try {
            CompteClient::delete($id);
            Flight::json(['message' => 'Compte client supprimé']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}
