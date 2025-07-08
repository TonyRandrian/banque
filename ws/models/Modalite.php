<?php
require_once __DIR__ . '/../db.php';

class Modalite {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT * FROM examS4_modalite";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByLibelle($libelle) {
        $db = getDB();
        $sql = "SELECT * FROM examS4_modalite where libelle = :libelle";
        $stmt = $db->prepare($sql);
        $stmt->execute(['libelle' => $libelle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
