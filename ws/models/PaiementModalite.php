<?php
require_once __DIR__ . '/../db.php';

class PaiementModalite {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT * FROM paiement_modalite";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByPret($pret_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM paiement_modalite WHERE pret_id = ?");
        $stmt->execute([$pret_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO paiement_modalite (date_prevu_paiment, montant_prevu, interet, amortissement, pret_id)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['date_prevu_paiment'],
            $data['montant_prevu'],
            $data['interet'],
            $data['amortissement'],
            $data['pret_id']
        ]);
        return $db->lastInsertId();
    }

    public static function deleteByPret($pret_id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM paiement_modalite WHERE pret_id = ?");
        $stmt->execute([$pret_id]);
    }
}
