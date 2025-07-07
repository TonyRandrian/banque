<?php
require_once __DIR__ . '/../../db.php';

class TypePret {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT * FROM type_pret";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
