<?php
require_once __DIR__ . '/../models/Fond.php';

class FondController
{
    public static function getAll()
    {
        $fonds = Fond::getAll();
        Flight::json($fonds);
    }

    public static function getById($id)
    {
        $fond = Fond::getById($id);
        Flight::json($fond);
    }

    public static function create()
    {
        $data = Flight::request()->data;
        $id = Fond::create($data);
        Flight::json(['message' => 'Fond ajouté', 'id' => $id]);
    }

    public static function update($id)
    {
        $data = Flight::request()->data;
        Fond::update($id, $data);
        Flight::json(['message' => 'Fond modifié']);
    }

    public static function delete($id)
    {
        Fond::delete($id);
        Flight::json(['message' => 'Fond supprimé']);
    }
}