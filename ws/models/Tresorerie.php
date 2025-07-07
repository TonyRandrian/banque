<?php
require_once __DIR__ . '/../db.php';

class Tresorerie {
    public static function getSoldeActuel() {
        $db = getDB();
        $stmt = $db->query("SELECT solde FROM tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? floatval($row['solde']) : 0;
    }
}
