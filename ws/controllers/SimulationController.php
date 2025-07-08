<?php
require_once __DIR__ . '/../models/SimulationPret.php';

class SimulationController
{
    public static function getAll()
    {
        try {
            $simulations = SimulationPret::getAll();
            Flight::json($simulations);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getTwo($id1, $id2)
    {
        try {
            $comparison = SimulationPret::compareTwo($id1, $id2);
            
            if (!$comparison) {
                Flight::json(['error' => 'Simulations non trouvées'], 404);
                return;
            }
            
            Flight::json($comparison);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
}
