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
            // Récupérer les données du body directement
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            // Debug : afficher les données reçues
            error_log("Données CompteClient create reçues : " . print_r($data, true));
            
            // Vérifier que tous les champs requis sont présents
            $requiredFields = ['nom', 'prenom', 'email', 'mdp', 'date_creation'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }
            
            // Vérifier le format email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Flight::json(['error' => 'Format email invalide'], 400);
                return;
            }
            
            $result = CompteClient::create($data);
            Flight::json(['message' => 'Compte client créé avec succès', 'data' => $result]);
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
            error_log("Données CompteClient update reçues : " . print_r($data, true));
            
            // Vérifier que les champs requis sont présents (sauf mdp qui est optionnel)
            $requiredFields = ['nom', 'prenom', 'email'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }
            
            // Vérifier le format email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Flight::json(['error' => 'Format email invalide'], 400);
                return;
            }
            
            CompteClient::update($id, $data);
            Flight::json(['message' => 'Compte client modifié avec succès']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function delete($id) {
        try {
            CompteClient::delete($id);
            Flight::json(['message' => 'Compte client supprimé avec succès']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}
