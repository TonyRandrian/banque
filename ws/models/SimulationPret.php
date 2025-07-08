<?php
require_once __DIR__ . '/../db.php';

class SimulationPret
{
    public static function create($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO simulation_pret (duree_remboursement, montant, date_demande, modalite_id, type_pret_id, taux_assurance, assurance_par_mois)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $data['modalite_id'],
            $data['type_pret_id'],
            $data['taux_assurance'] ?? 0.00,
            $data['assurance_par_mois'] ?? 0
        ]);
        $id = $db->lastInsertId();
        // Retourner l'objet inséré
        $stmt = $db->prepare("SELECT * FROM simulation_pret WHERE id = ?");
        $stmt->execute([$id]);
        $pret = $stmt->fetch(PDO::FETCH_ASSOC);
        return $pret;
    }
}