<?php
require_once __DIR__ . '/../controllers/SimulationPaiementModaliteController.php';

Flight::route('POST /simulation_paiement_modalites', ['SimulationPaiementModaliteController', 'create']);