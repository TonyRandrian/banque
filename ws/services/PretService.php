<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Modalite.php';
require_once __DIR__ . '/../models/TypePret.php';
require_once __DIR__ . '/../models/Tresorerie.php';

class PretService {
    public static function getAll() {
        return Pret::allWithRelations();
    }

    public static function getById($id) {
        return Pret::findWithRelations($id);
    }

    public static function create($data) {
        // Vérification du solde disponible
        $montant = isset($data['montant']) ? floatval($data['montant']) : 0;
        $solde = Tresorerie::getSoldeActuel();
        if ($montant > $solde) {
            http_response_code(400);
            return ['error' => "Solde insuffisant en trésorerie (solde actuel : $solde, montant demandé : $montant)"];
        }
        return Pret::create($data);
    }

    public static function update($id, $data) {
        return Pret::update($id, $data);
    }

    public static function delete($id) {
        return Pret::delete($id);
    }

    public static function getModalites() {
        return Modalite::getAll();
    }

    public static function getTypePrets() {
        return TypePret::getAll();
    }
}
