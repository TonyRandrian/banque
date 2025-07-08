<?php
require_once __DIR__ . '/../services/PretService.php';
require_once __DIR__ . '/../models/StatusPret.php';
require_once __DIR__ . '/../models/Fond.php';
require_once __DIR__ . '/../models/SimulationPret.php';

class SimulationPretController
{
    public static function create()
    {
        try {
            $db = getDB();
            $input = file_get_contents('php://input');
            parse_str($input, $data);

            $modaliteStmt = $db->prepare("SELECT id FROM examS4_modalite WHERE libelle = :libelle or libelle = :libelle2");
            $modaliteStmt->execute(['libelle' => 'Annuelle', 'libelle2'=>'Annuel']);
            $modalite = $modaliteStmt->fetch(PDO::FETCH_ASSOC);

            $data['modalite_id'] = $modalite['id'];

            error_log("Données reçues : " . print_r($data, true));

            $requiredFields = ['duree_remboursement', 'montant', 'date_demande', 'modalite_id', 'type_pret_id'];
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
                $data['assurance_par_mois'] = false;
            }

            $result = SimulationPret::create($data);

            // Debug : log le résultat pour vérifier la structure
            error_log("Résultat de SimulationPret::create : " . print_r($result, true));
            $result = (array)$result;
            if (!isset($result['id'])) {
                error_log("Aucun id trouvé dans le résultat de SimulationPret::create !");
            }

            Flight::json(['message' => 'Prêt ajouté avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}
