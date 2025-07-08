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
        $stmt = $db->prepare("INSERT INTO paiement_modalite (date_prevu_paiment, montant_prevu, mensualite, interet, amortissement, assurance, montant_restant, pret_id)
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

    public static function deleteByPret($pret_id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM paiement_modalite WHERE pret_id = ?");
        $stmt->execute([$pret_id]);
    }
    
    /**
     * Calculer le total des paiements modalité jusqu'à une date donnée
     */
    public static function getTotalByDate($date) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT SUM(montant_prevu) as total FROM paiement_modalite WHERE date_prevu_paiment <= ?");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Gérer le cas où il n'y a pas de résultats ou total est NULL
            if ($result && isset($result['total']) && $result['total'] !== null) {
                return floatval($result['total']);
            }
            
            return 0;
            
        } catch (Exception $e) {
            error_log("Erreur dans getTotalByDate: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculer le total des paiements modalité par mois pour une période
     */
    public static function getTotalByMonth($dateDebut, $dateFin) {
        $db = getDB();
        $sql = "SELECT 
                    DATE_FORMAT(date_prevu_paiment, '%Y-%m') as mois,
                    SUM(montant_prevu) as total
                FROM paiement_modalite 
                WHERE date_prevu_paiment BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(date_prevu_paiment, '%Y-%m')
                ORDER BY mois";
        $stmt = $db->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
