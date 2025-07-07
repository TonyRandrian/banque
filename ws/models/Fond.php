<?php
require_once __DIR__ . '/../db.php';

class Fond
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM tresorerie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM tresorerie WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO tresorerie (solde, date_mouvement) VALUES (?, ?)");
        $stmt->execute([$data->montant, $data->date_creation]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE tresorerie SET solde = ?, date_mouvement = ? WHERE id = ?");
        $stmt->execute([$data->montant, $data->date_creation, $id]);
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM tresorerie WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function getLast()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM tresorerie ORDER BY date_mouvement DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}