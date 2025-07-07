<?php
require_once __DIR__ . '/../db.php';

class TypePret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM type_pret ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM type_pret WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO type_pret (libelle, taux, taux_assurance) VALUES (?, ?, ?)");
        $stmt->execute([$data->libelle, $data->taux, $data->taux_assurance ?? 0.00]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE type_pret SET libelle = ?, taux = ?, taux_assurance = ? WHERE id = ?");
        $stmt->execute([$data->libelle, $data->taux, $data->taux_assurance ?? 0.00, $id]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM type_pret WHERE id = ?");
        $stmt->execute([$id]);
    }
}
