<?php
require_once __DIR__ . '/../services/PretService.php';
require_once __DIR__ . '/../models/StatusPret.php';

class PretController {
    public static function getAll() {
        try {
            $prets = PretService::getAll();
            Flight::json($prets);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getById($id) {
        try {
            $pret = PretService::getById($id);
            Flight::json($pret);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function create() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);

            error_log("Données reçues : " . print_r($data, true));

            $requiredFields = ['duree_remboursement', 'montant', 'date_demande', 'modalite_id', 'type_pret_id', 'compte_client_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }
            if (!isset($data['taux_assurance'])) {
                $data['taux_assurance'] = 0.00;
            }
            if (!isset($data['assurance_par_mois'])) {
                $data['assurance_par_mois'] = 0;
            }

            $result = PretService::create($data);

            // Ajout du status "Accepté" pour ce prêt via le modèle StatusPret
            if (isset($result['id'])) {
                $pret_id = $result['id'];
                $db = getDB();
                $enumStmt = $db->prepare("SELECT id FROM enum_status_pret WHERE libelle = 'Accepté' LIMIT 1");
                $enumStmt->execute();
                $enum = $enumStmt->fetch(PDO::FETCH_ASSOC);
                $enum_id = $enum ? $enum['id'] : null;
                if ($enum_id) {
                    StatusPret::create([
                        'date_status' => date('Y-m-d'),
                        'enum_pret_id' => $enum_id,
                        'pret_id' => $pret_id
                    ]);
                }
            }

            Flight::json(['message' => 'Prêt ajouté avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function update($id) {
        try {
            $data = Flight::request()->data;

            if (empty($data->duree_remboursement) || empty($data->montant)) {
                $input = file_get_contents('php://input');
                parse_str($input, $parsedData);

                if (!empty($parsedData['duree_remboursement']) && !empty($parsedData['montant'])) {
                    $data = $parsedData;
                } else {
                    $data = (array) $data;
                }
            } else {
                $data = (array) $data;
            }

            if (empty($data['duree_remboursement']) || empty($data['montant']) || empty($data['date_demande']) || empty($data['modalite_id']) || empty($data['type_pret_id']) || empty($data['compte_client_id'])) {
                Flight::json(['error' => 'Tous les champs sont requis'], 400);
                return;
            }
            if (!isset($data['taux_assurance'])) {
                $data['taux_assurance'] = 0.00;
            }
            if (!isset($data['assurance_par_mois'])) {
                $data['assurance_par_mois'] = 0;
            }

            $result = PretService::update($id, $data);
            Flight::json(['message' => 'Prêt modifié avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function delete($id) {
        try {
            $result = PretService::delete($id);
            Flight::json(['message' => 'Prêt supprimé avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getModalites() {
        echo json_encode(PretService::getModalites());
    }

    public static function getTypePrets() {
        echo json_encode(PretService::getTypePrets());
    }
}
