<?php
require_once __DIR__ . '/../db.php';

class Pret
{
    public static function allWithRelations()
    {
        $db = getDB();
        $sql = "SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle, cc.numero AS compte_client_numero
                FROM pret p
                JOIN modalite m ON p.modalite_id = m.id
                JOIN type_pret t ON p.type_pret_id = t.id
                JOIN compte_client cc ON p.compte_client_id = cc.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allWithRelationsValid()
    {
        $db = getDB();
        $sql = "SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle, 
                       cc.numero AS compte_client_numero, esp.libelle AS status_libelle,
                       s.date_status, COUNT(pm.id) as nb_paiements
                FROM pret p
                JOIN modalite m ON p.modalite_id = m.id
                JOIN type_pret t ON p.type_pret_id = t.id
                JOIN compte_client cc ON p.compte_client_id = cc.id
                JOIN (
                    SELECT pret_id, MAX(date_status) as max_date_status
                    FROM status_pret sp
                    JOIN enum_status_pret esp ON sp.enum_pret_id = esp.id
                    WHERE esp.libelle = 'accepté'
                    GROUP BY pret_id
                ) latest_status ON p.id = latest_status.pret_id
                JOIN status_pret s ON p.id = s.pret_id AND s.date_status = latest_status.max_date_status
                JOIN enum_status_pret esp ON s.enum_pret_id = esp.id
                JOIN paiement_modalite pm ON p.id = pm.pret_id
                WHERE esp.libelle = 'accepté'
                GROUP BY p.id, p.duree_remboursement, p.montant, p.date_demande, p.modalite_id, 
                         p.compte_client_id, p.type_pret_id, p.taux_assurance, p.assurance_par_mois,
                         m.libelle, t.libelle, cc.numero, esp.libelle, s.date_status
                HAVING COUNT(pm.id) > 0
                ORDER BY s.date_status DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithRelations($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, 
                                     m.libelle AS modalite_libelle, 
                                     t.libelle AS type_pret_libelle, 
                                     cc.numero AS compte_numero,
                                     c.nom AS client_nom,
                                     c.prenom AS client_prenom
                              FROM pret p
                              JOIN modalite m ON p.modalite_id = m.id
                              JOIN type_pret t ON p.type_pret_id = t.id
                              JOIN compte_client cc ON p.compte_client_id = cc.id
                              JOIN client c ON cc.client_id = c.id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret (duree_remboursement, montant, date_demande, modalite_id, type_pret_id, taux_assurance, assurance_par_mois, compte_client_id)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $data['modalite_id'],
            $data['type_pret_id'],
            $data['taux_assurance'] ?? 0.00,
            $data['assurance_par_mois'] ?? 0,
            $data['compte_client_id']
        ]);
        $id = $db->lastInsertId();
        // Retourner l'objet inséré
        $stmt = $db->prepare("SELECT * FROM pret WHERE id = ?");
        $stmt->execute([$id]);
        $pret = $stmt->fetch(PDO::FETCH_ASSOC);
        return $pret;
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET duree_remboursement=?, montant=?, date_demande=?, modalite_id=?, type_pret_id=?, taux_assurance=?, assurance_par_mois=?, compte_client_id=?
                              WHERE id=?");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $data['modalite_id'],
            $data['type_pret_id'],
            $data['taux_assurance'] ?? 0.00,
            $data['assurance_par_mois'] ?? 0,
            $data['compte_client_id'],
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