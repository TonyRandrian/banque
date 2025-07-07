<?php
require_once __DIR__ . '/../db.php';

class EnumStatusPret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM enum_status_pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
