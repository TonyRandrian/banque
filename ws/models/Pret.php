<?php
require_once __DIR__ . '/../db.php';

class Pret {
    public static function allWithRelations() {
        $db = getDB();
        $sql = "SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle, cc.numero AS compte_client_numero
                FROM pret p
                JOIN modalite m ON p.modalite_id = m.id
                JOIN type_pret t ON p.type_pret_id = t.id
                JOIN compte_client cc ON p.compte_client_id = cc.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithRelations($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle, cc.numero AS compte_client_numero
                              FROM pret p
                              JOIN modalite m ON p.modalite_id = m.id
                              JOIN type_pret t ON p.type_pret_id = t.id
                              JOIN compte_client cc ON p.compte_client_id = cc.id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret (duree_remboursement, montant, date_demande, modalite_id, type_pret_id, taux_assurance, assurance_par_mois, compte_client_id)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $modalite = Modalite::findByLibelle("Annuelle");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $modalite,
            $data['type_pret_id'],
            $data['taux_assurance'] ?? 0.00,
            $data['assurance_par_mois'] ?? 0,
            $data['compte_client_id']
        ]);
        return ['id' => $db->lastInsertId()];
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET duree_remboursement=?, montant=?, date_demande=?, modalite_id=?, type_pret_id=?, taux_assurance=?, assurance_par_mois=?, compte_client_id=?
                              WHERE id=?");
        $modalite = Modalite::findByLibelle("Annuelle");
        $stmt->execute([
            $data['duree_remboursement'],
            $data['montant'],
            $data['date_demande'],
            $modalite,
            $data['type_pret_id'],
            $data['taux_assurance'] ?? 0.00,
            $data['assurance_par_mois'] ?? 0,
            $data['compte_client_id'],
            $id
        ]);
        return ['success' => true];
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id=?");
        $stmt->execute([$id]);
        return ['success' => true];
    }
}
