<?php
require_once __DIR__ . '/../db.php';

class StatusPret {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT sp.*, esp.libelle AS enum_libelle 
                FROM status_pret sp
                JOIN enum_status_pret esp ON sp.enum_pret_id = esp.id
                ORDER BY sp.date_status DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $sql = "SELECT sp.*, esp.libelle AS enum_libelle 
                FROM status_pret sp
                JOIN enum_status_pret esp ON sp.enum_pret_id = esp.id
                WHERE sp.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO status_pret (date_status, enum_pret_id, pret_id) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['date_status'],
            $data['enum_pret_id'],
            $data['pret_id']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE status_pret SET date_status = ?, enum_pret_id = ?, pret_id = ? WHERE id = ?");
        $stmt->execute([
            $data['date_status'],
            $data['enum_pret_id'],
            $data['pret_id'],
            $id
        ]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM status_pret WHERE id = ?");
        $stmt->execute([$id]);
    }
}
