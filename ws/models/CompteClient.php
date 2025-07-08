<?php
require_once __DIR__ . '/../db.php';

class CompteClient {
    public static function getAll() {
        $db = getDB();
        // ...existing code...
        // Correction : la table client a bien nom, prenom, email
        $sql = "SELECT * FROM compte_client";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT cc.id, cc.numero, cc.date_creation, cc.client_id, c.nom, c.prenom, c.email
                              FROM compte_client cc
                              JOIN client c ON cc.client_id = c.id
                              WHERE cc.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO compte_client (numero, date_creation, client_id) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['numero'],
            $data['date_creation'],
            $data['client_id']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE compte_client SET numero=?, date_creation=?, client_id=? WHERE id=?");
        $stmt->execute([
            $data['numero'],
            $data['date_creation'],
            $data['client_id'],
            $id
        ]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM compte_client WHERE id=?");
        $stmt->execute([$id]);
    }
}
