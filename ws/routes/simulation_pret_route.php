<?php
require_once __DIR__ . '/../controllers/SimulationPretController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';

Flight::route('POST /simulation_prets', ['SimulationPretController', 'create']);