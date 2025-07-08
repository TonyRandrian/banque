<?php
require_once __DIR__ . '/../models/PaiementModalite.php';
require_once __DIR__ . '/../models/Fond.php';

class PaiementModaliteController {
    public static function getAll() {
        $data = PaiementModalite::getAll();
        Flight::json($data);
    }

    public static function getByPret($pret_id) {
        $data = PaiementModalite::getByPret($pret_id);
        Flight::json($data);
    }

    public static function create() {
        $input = file_get_contents('php://input');
        parse_str($input, $data);

        // ...vérification des champs requis...
        // if (
        //     empty($data['date_prevu_paiment']) || empty($data['montant_prevu']) ||
        //     empty($data['mensualite']) || empty($data['interet']) ||
        //     empty($data['amortissement']) || !isset($data['assurance']) ||
        //     empty($data['montant_restant']) || empty($data['pret_id'])
        // ) {
        //     Flight::json(['error' => 'Tous les champs sont requis'], 400);
        //     return;
        // }

        // Vérification de la date du mouvement
        $lastDate = Fond::getLastDate();
        if ($lastDate !== null && $data['date_prevu_paiment'] < $lastDate) {
            Flight::json(['error' => "La date du paiement doit être supérieure ou égale à la dernière date de mouvement ($lastDate)"], 400);
            return;
        }

        $id = PaiementModalite::create($data);

        // Ajout dans la table tresorerie (crédit du montant prévu à la date prévue) via Fond
        try {
            Fond::insertMouvement($data['montant_prevu'], $data['date_prevu_paiment']);
        } catch (Exception $e) {
            error_log("Erreur insertion tresorerie pour paiement_modalite: " . $e->getMessage());
        }

        Flight::json(['message' => 'Paiement modalité ajouté', 'id' => $id]);
    }

    public static function deleteByPret($pret_id) {
        PaiementModalite::deleteByPret($pret_id);
        Flight::json(['message' => 'Paiements supprimés']);
    }
}
