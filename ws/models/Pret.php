<?php
require_once __DIR__ . '/../../db.php';

class Pret {
    public static function allWithRelations() {
        $db = getDB();
        $sql = "SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle
                FROM pret p
                JOIN modalite m ON p.modalite_id = m.id
                JOIN type_pret t ON p.type_pret_id = t.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithRelations($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, m.libelle AS modalite_libelle, t.libelle AS type_pret_libelle
                              FROM pret p
                              JOIN modalite m ON p.modalite_id = m.id
                              JOIN type_pret t ON p.type_pret_id = t.id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
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

    public static function update($id, $data) {
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

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id=?");
        $stmt->execute([$id]);
        return ['success' => true];
    }
}
