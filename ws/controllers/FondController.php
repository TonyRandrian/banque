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
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);

            /*// Validation
            if (!isset($data['montant']) || floatval($data['montant']) <= 0) {
                Flight::json(['error' => 'Veuillez saisir un montant valide supérieur à 0'], 400);
                return;
            }*/
            if (empty($data['date_creation'])) {
                Flight::json(['error' => 'Veuillez choisir une date de mouvement'], 400);
                return;
            }

            // Vérification de la date du mouvement
            $lastDate = Fond::getLastDate();
            if ($lastDate !== null && $data['date_creation'] < $lastDate) {
                Flight::json(['error' => "La date du mouvement doit être supérieure ou égale à la dernière date de mouvement ($lastDate)"], 400);
                return;
            }

            Fond::create($data);

            Flight::json(['message' => 'Mouvement ajouté avec succès']);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}