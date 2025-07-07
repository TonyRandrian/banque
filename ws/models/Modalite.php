<?php
require_once __DIR__ . '/../db.php';

class Modalite {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT * FROM modalite";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
