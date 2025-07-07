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
        $last = Fond::getLast();
        $lastSolde = $last ? floatval($last['solde']) : 0;
        $newSolde = $lastSolde + floatval($data->montant);

        // Générer la date du jour côté PHP
        $today = date('Y-m-d');
        $insertData = (object)[
            'montant' => $newSolde,
            'date_creation' => $today
        ];
        $id = Fond::create($insertData);
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