<?php
require_once __DIR__ . '/../models/SimulationPaiementModalite.php';
require_once __DIR__ . '/../models/Fond.php';

class SimulationPaiementModaliteController {

    public static function create() {
        $input = file_get_contents('php://input');
        parse_str($input, $data);

        $id = SimulationPaiementModalite::create($data);

        Flight::json(['message' => 'Paiement modalité ajouté', 'id' => $id]);
    }
}
