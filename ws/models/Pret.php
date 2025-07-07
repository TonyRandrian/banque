<?php
require_once __DIR__ . '/../db.php';

class Pret
{
    public static function allWithRelations()
    {
        $db = getDB();
        $sql = "SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle
                FROM pret p
                JOIN modalite m ON p.modalite_id = m.id
                JOIN type_pret t ON p.type_pret_id = t.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithRelations($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle
                              FROM pret p
                              JOIN modalite m ON p.modalite_id = m.id
                              JOIN type_pret t ON p.type_pret_id = t.id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret (duree_remboursement, montant, date_demande, modalite_id, type_pret_id)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $data['modalite_id'],
            $data['type_pret_id']
        ]);
        return ['id' => $db->lastInsertId()];
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET duree_remboursement=?, montant=?, date_demande=?, modalite_id=?, type_pret_id=?
                              WHERE id=?");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $data['modalite_id'],
            $data['type_pret_id'],
            $id
        ]);
        return ['success' => true];
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id=?");
        $stmt->execute([$id]);
        return ['success' => true];
    }

    public static function getMensualiteParPeriode($pret_id, $nb_periode = 12)
    {
        $db = getDB();

        // Récupérer le montant, le type_pret_id et la modalite_id pour ce prêt
        $stmt = $db->prepare("SELECT montant, type_pret_id, modalite_id, duree_remboursement FROM pret WHERE id = ?");
        $stmt->execute([$pret_id]);
        $pret = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pret) return null;

        // Récupérer le taux d'intérêt du type de prêt
        $stmt = $db->prepare("SELECT taux FROM type_pret WHERE id = ?");
        $stmt->execute([$pret['type_pret_id']]);
        $typePret = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$typePret) return null;

        $C = floatval($pret['montant']);
        $taux_annuel = floatval($typePret['taux']);
        $n = intval($pret['duree_remboursement']);

        // Conversion du taux annuel en taux par la période
        $t = $taux_annuel / 100 / $nb_periode;

        if ($t == 0)
            // Si taux nul, mensualité = capital / nombre de mois
            $mensualite = $C / $n;
        else
            $mensualite = $C * ($t / (1 - pow(1 + $t, -$n)));

        return round($mensualite, 2);
    }

    /**
     * Retourne les intérêts par mois pour un prêt donné, filtrés par période.
     * @param int $pret_id
     * @param string $date_debut format 'YYYY-MM'
     * @param string $date_fin format 'YYYY-MM'
     * @return array [ 'YYYY-MM' => montant_interets, ... ]
     */
    public static function getInteretsParMoisPourPret($pret_id, $date_debut, $date_fin)
    {
        $db = getDB();
        // Vérifier que le prêt est accepté (enum_pret_id = 2)
        $stmt = $db->prepare("
            SELECT MIN(date_status) AS date_acceptation
            FROM status_pret
            WHERE pret_id = ? AND enum_pret_id = 2
        ");
        $stmt->execute([$pret_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['date_acceptation']) {
            return [];
        }

        // Agréger par année et mois
        $stmt = $db->prepare("
            SELECT YEAR(date_prevu_paiment) as annee, MONTH(date_prevu_paiment) as mois, SUM(interet) as interet
            FROM paiement_modalite
            WHERE pret_id = ?
              AND DATE_FORMAT(date_prevu_paiment, '%Y-%m') >= ?
              AND DATE_FORMAT(date_prevu_paiment, '%Y-%m') <= ?
              AND date_prevu_paiment >= ?
            GROUP BY annee, mois
            ORDER BY annee, mois
        ");

        $stmt->execute([$pret_id, $date_debut, $date_fin, $row['date_acceptation']]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = $row['annee'] . '-' . str_pad($row['mois'], 2, '0', STR_PAD_LEFT);
            $result[$key] = round($row['interet'], 2);
        }

        return $result;
    }

    /**
     * Retourne les intérêts par mois pour tous les prêts acceptés, filtrés par période.
     * @param string $date_debut format 'YYYY-MM'
     * @param string $date_fin format 'YYYY-MM'
     * @return array [ 'YYYY-MM' => [ 'interet' => montant, 'amortissement' => montant, 'mensualite' => montant ], ... ]
     */
    public static function getInteretsParMoisTousPrets($date_debut, $date_fin)
    {
        $db = getDB();
        $sql = "
            SELECT YEAR(pm.date_prevu_paiment) as annee, MONTH(pm.date_prevu_paiment) as mois, SUM(pm.interet) as interet
            FROM paiement_modalite pm
            JOIN pret p ON pm.pret_id = p.id
            JOIN (
                SELECT pret_id, MIN(date_status) AS date_acceptation
                FROM status_pret
                WHERE enum_pret_id = 2
                GROUP BY pret_id
            ) sp ON sp.pret_id = p.id
            WHERE DATE_FORMAT(pm.date_prevu_paiment, '%Y-%m') >= ?
              AND DATE_FORMAT(pm.date_prevu_paiment, '%Y-%m') <= ?
              AND pm.date_prevu_paiment >= sp.date_acceptation
            GROUP BY annee, mois
            ORDER BY annee, mois
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$date_debut, $date_fin]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = $row['annee'] . '-' . str_pad($row['mois'], 2, '0', STR_PAD_LEFT);
            $result[$key] = round($row['interet'], 2);
        }
        return $result;
    }

    /**
     * Retourne la liste des prêts acceptés avec leurs informations de base
     * @return array
     */
    public static function getPretsAcceptes()
    {
        $db = getDB();
        $sql = "
            SELECT DISTINCT p.id, p.montant, p.date_demande, 
                   m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle,
                   sp.date_acceptation
            FROM pret p
            JOIN modalite m ON p.modalite_id = m.id
            JOIN type_pret t ON p.type_pret_id = t.id
            JOIN (
                SELECT pret_id, MIN(date_status) AS date_acceptation
                FROM status_pret
                WHERE enum_pret_id = 2
                GROUP BY pret_id
            ) sp ON sp.pret_id = p.id
            ORDER BY sp.date_acceptation
        ";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les intérêts gagnés par mois pour l'établissement financier, filtrés par période.
     * (Méthode de compatibilité - utilise getInteretsParMoisTousPrets mais ne retourne que les intérêts)
     * @param string $date_debut format 'YYYY-MM'
     * @param string $date_fin format 'YYYY-MM'
     * @return array [ 'YYYY-MM' => montant_interets, ... ]
     */
    public static function getInteretsParMois($date_debut, $date_fin)
    {
        return self::getInteretsParMoisTousPrets($date_debut, $date_fin);
    }
}