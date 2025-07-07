<?php
require_once __DIR__ . '/../models/TypePret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class TypePretController {
    public static function getAll() {
        try {
            $typePrets = TypePret::getAll();
            Flight::json($typePrets);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getById($id) {
        try {
            $typePret = TypePret::getById($id);
            Flight::json($typePret);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function create() {
        try {
            $data = Flight::request()->data;
            
            // Si les données ne sont pas dans data, essayer de parser le body
            if (empty($data->libelle) || empty($data->taux)) {
                $input = file_get_contents('php://input');
                parse_str($input, $parsedData);
                
                if (!empty($parsedData['libelle']) && !empty($parsedData['taux'])) {
                    $data = (object) $parsedData;
                }
            }
            
            if (empty($data->libelle) || empty($data->taux)) {
                Flight::json(['error' => 'Libellé et taux sont requis'], 400);
                return;
            }
            
            // Définir taux_assurance par défaut si non fourni
            if (!isset($data->taux_assurance)) {
                $data->taux_assurance = 0.00;
            }
            
            $id = TypePret::create($data);
            Flight::json(['message' => 'Type de prêt ajouté', 'id' => $id]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function update($id) {
        try {
            $data = Flight::request()->data;
            
            // Si les données ne sont pas dans data, essayer de parser le body
            if (empty($data->libelle) || empty($data->taux)) {
                $input = file_get_contents('php://input');
                parse_str($input, $parsedData);
                
                if (!empty($parsedData['libelle']) && !empty($parsedData['taux'])) {
                    $data = (object) $parsedData;
                }
            }
            
            if (empty($data->libelle) || empty($data->taux)) {
                Flight::json(['error' => 'Libellé et taux sont requis'], 400);
                return;
            }
            
            // Définir taux_assurance par défaut si non fourni
            if (!isset($data->taux_assurance)) {
                $data->taux_assurance = 0.00;
            }
            
            TypePret::update($id, $data);
            Flight::json(['message' => 'Type de prêt modifié']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function delete($id) {
        try {
            TypePret::delete($id);
            Flight::json(['message' => 'Type de prêt supprimé']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}
