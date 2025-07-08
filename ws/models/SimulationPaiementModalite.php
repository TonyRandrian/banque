<?php
require_once __DIR__ . '/../db.php';

class SimulationPaiementModalite {
    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO simulation_paiement_modalite (date_prevu_paiment, montant_prevu, mensualite, interet, amortissement, assurance, montant_restant, simulation_pret_id)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['date_prevu_paiment'],
            $data['montant_prevu'],
            $data['mensualite'],
            $data['interet'],
            $data['amortissement'],
            $data['assurance'],
            $data['montant_restant'],
            $data['pret_id']
        ]);
        return $db->lastInsertId();
    }
}
