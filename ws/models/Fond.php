<?php
require_once __DIR__ . '/../db.php';

class Fond
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM tresorerie ORDER BY date_mouvement DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM tresorerie WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getLastSolde()
    {
        $db = getDB();
        $stmt = $db->query("SELECT solde FROM tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1");
        $solde = $stmt->fetchColumn();
        return $solde === false ? 0 : floatval($solde);
    }

    public static function getLastDate()
    {
        $db = getDB();
        $stmt = $db->query("SELECT date_mouvement FROM tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1");
        $date = $stmt->fetchColumn();
        return $date === false ? null : $date;
    }

    public static function insertMouvement($montant, $date_creation)
    {
        $db = getDB();
        $lastSolde = self::getLastSolde();
        $lastDate = self::getLastDate();

        // Vérification de la date
        if ($lastDate !== null && $date_creation < $lastDate) {
            throw new Exception("La date du mouvement doit être supérieure ou égale à la dernière date de mouvement ($lastDate)");
        }

        $nouveauSolde = $lastSolde + floatval($montant);
        $stmt = $db->prepare("INSERT INTO tresorerie (date_mouvement, solde) VALUES (?, ?)");
        $stmt->execute([$date_creation, $nouveauSolde]);
        return $db->lastInsertId();
    }

    public static function create($data)
    {
        // $data doit contenir 'montant' et 'date_creation'
        return self::insertMouvement($data['montant'], $data['date_creation']);
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
}