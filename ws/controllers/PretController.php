<?php
require_once __DIR__ . '/../services/PretService.php';
require_once __DIR__ . '/../models/StatusPret.php';
require_once __DIR__ . '/../models/Fond.php';

class PretController
{
    public static function getAll()
    {
        try {
            $prets = PretService::getAll();
            Flight::json($prets);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }


    public static function getValide()
    {
        try {
            $prets = PretService::getAllValide();
            Flight::json($prets);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getById($id)
    {
        try {
            $pret = PretService::getById($id);
            Flight::json($pret);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function create()
    {
        try {
            $db = getDB();
            $input = file_get_contents('php://input');
            parse_str($input, $data);

            $modaliteStmt = $db->prepare("SELECT id FROM modalite WHERE libelle = :libelle or libelle = :libelle2");
            $modaliteStmt->execute(['libelle' => 'Annuelle', 'libelle2'=>'Annuel']);
            $modalite = $modaliteStmt->fetch(PDO::FETCH_ASSOC);

            $data['modalite_id'] = $modalite['id'];

            error_log("Données reçues : " . print_r($data, true));

            $requiredFields = ['duree_remboursement', 'montant', 'date_demande', 'modalite_id', 'type_pret_id', 'compte_client_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    Flight::json(['error' => "Le champ '$field' est requis"], 400);
                    return;
                }
            }

            // Vérification du solde de la trésorerie
            $moisDemande = substr($data['date_demande'], 0, 7); // Extraire YYYY-MM de YYYY-MM-DD
            $solde = Fond::getSoldeForMonth($moisDemande);
            if (floatval($data['montant']) > floatval($solde)) {
                Flight::json(['error' => "Solde de la trésorerie insuffisant pour accorder ce prêt pour " . $data["date_demande"] . " (solde actuel : " . number_format($solde, 2, ',', ' ') . " €)"], 400);
                return;
            }

            // Vérification de la date du mouvement
            $lastDate = Fond::getLastDate();
            if ($lastDate !== null && $data['date_demande'] < $lastDate) {
                Flight::json(['error' => "La date du prêt doit être supérieure ou égale à la dernière date de mouvement ($lastDate)"], 400);
                return;
            }

            if (!isset($data['taux_assurance'])) {
                $data['taux_assurance'] = 0.00;
            }

            if (!isset($data['assurance_par_mois'])) {
                $data['assurance_par_mois'] = false;
            }

            $result = PretService::create($data);

            // Debug : log le résultat pour vérifier la structure
            error_log("Résultat de PretService::create : " . print_r($result, true));
            $result = (array)$result;

            // Ajout du status "Accepté" pour ce prêt via le modèle StatusPret
            if (
                (is_array($result) && isset($result['id']) && $result['id']) ||
                (is_object($result) && isset($result->id) && $result->id)
            ) {
                $pret_id = is_array($result) ? $result['id'] : $result->id;

                $enumStmt = $db->prepare("SELECT id FROM enum_status_pret WHERE libelle = 'Accepte' LIMIT 1");
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

                // Soustraction du solde et insertion du nouveau solde via Fond
                Fond::insertMouvement(-floatval($data['montant']), $data['date_demande']);
            } else {
                error_log("Aucun id trouvé dans le résultat de PretService::create !");
            }

            Flight::json(['message' => 'Prêt ajouté avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function update($id)
    {
        try {
            $data = Flight::request()->data;

            if (empty($data->duree_remboursement) || empty($data->montant)) {
                $input = file_get_contents('php://input');
                parse_str($input, $parsedData);

                if (!empty($parsedData['duree_remboursement']) && !empty($parsedData['montant'])) {
                    $data = $parsedData;
                } else {
                    $data = (array)$data;
                }
            } else {
                $data = (array)$data;
            }

            if (empty($data['duree_remboursement']) || empty($data['montant']) || empty($data['date_demande']) || empty($data['modalite_id']) || empty($data['type_pret_id']) || empty($data['compte_client_id'])) {
                Flight::json(['error' => 'Tous les champs sont requis'], 400);
                return;
            }

            // Vérification de la date du mouvement
            $lastDate = Fond::getLastDate();
            if ($lastDate !== null && $data['date_demande'] < $lastDate) {
                Flight::json(['error' => "La date du prêt doit être supérieure ou égale à la dernière date de mouvement ($lastDate)"], 400);
                return;
            }

            // Vérification du solde de la trésorerie
            $db = getDB();
            $solde = $db->query("SELECT solde FROM tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1")->fetchColumn();
            if ($solde === false) $solde = 0;
            if (floatval($data['montant']) > floatval($solde)) {
                Flight::json(['error' => "Solde de la trésorerie insuffisant pour accorder ce prêt (solde actuel : " . number_format($solde, 2, ',', ' ') . " €)"], 400);
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

    public static function delete($id)
    {
        try {
            $result = PretService::delete($id);
            Flight::json(['message' => 'Prêt supprimé avec succès', 'data' => $result]);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getModalites()
    {
        echo json_encode(PretService::getModalites());
    }

    public static function getTypePrets()
    {
        echo json_encode(PretService::getTypePrets());
    }

    /**
     * Web service pour les intérêts gagnés par mois (POST)
     * Attend : date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMois()
    {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;
        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMois($date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour les intérêts gagnés par mois pour tous les prêts (POST)
     * Attend : date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMoisTousPrets()
    {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;

        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }

        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMoisTousPrets($date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour les intérêts gagnés par mois pour un prêt spécifique (POST)
     * Attend : pret_id, date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMoisPourPret($pret_id)
    {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;

        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }

        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMoisPourPret($pret_id, $date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour obtenir la liste des prêts acceptés
     */
    public static function getPretsAcceptes()
    {
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getPretsAcceptes();
        echo json_encode($result);
    }

    /**
     * Web service pour exporter un prêt en PDF
     */
    public static function exportPDF($id)
    {
        try {
            $pret = PretService::getById($id);
            if (!$pret) {
                Flight::json(['error' => 'Prêt non trouvé'], 404);
                return;
            }

            require_once __DIR__ . '/../models/PaiementModalite.php';
            require_once __DIR__ . '/../services/PretPDF.php';
            $paiements = PaiementModalite::getByPret($id);

            $pdfData = PretPDF::genererPDF($pret, $paiements);

            // Envoyer le PDF avec les bons headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="pret_' . $id . '.pdf"');
            header('Content-Length: ' . strlen($pdfData));
            echo $pdfData;
            exit;
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

}
