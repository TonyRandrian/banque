<?php
require_once __DIR__ . '/../models/PaiementModalite.php';

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
        if (
            empty($data['date_prevu_paiment']) || empty($data['montant_prevu']) ||
            empty($data['mensualite']) || empty($data['interet']) ||
            empty($data['amortissement']) || !isset($data['assurance']) ||
            empty($data['montant_restant']) || empty($data['pret_id'])
        ) {
            Flight::json(['error' => 'Tous les champs sont requis'], 400);
            return;
        }
        $id = PaiementModalite::create($data);
        Flight::json(['message' => 'Paiement modalité ajouté', 'id' => $id]);
    }

    public static function deleteByPret($pret_id) {
        PaiementModalite::deleteByPret($pret_id);
        Flight::json(['message' => 'Paiements supprimés']);
    }
}
