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
        try {
            // Récupérer les données du body directement
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            // Debug : afficher les données reçues
            error_log("Données StatusPret create reçues : " . print_r($data, true));
            
            // Vérifier que tous les champs requis sont présents
            $requiredFields = ['date_status', 'enum_pret_id', 'pret_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }
            
            $id = StatusPret::create($data);
            Flight::json(['message' => 'Status prêt ajouté', 'id' => $id]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function update($id) {
        try {
            // Récupérer les données du body directement
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            // Debug : afficher les données reçues
            error_log("Données StatusPret update reçues : " . print_r($data, true));
            
            // Vérifier que tous les champs requis sont présents
            $requiredFields = ['date_status', 'enum_pret_id', 'pret_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }
            
            StatusPret::update($id, $data);
            Flight::json(['message' => 'Status prêt modifié']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function delete($id) {
        StatusPret::delete($id);
        Flight::json(['message' => 'Status prêt supprimé']);
    }
}
