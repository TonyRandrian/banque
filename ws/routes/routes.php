<?php
require_once __DIR__ . '/../controllers/FondController.php';

Flight::route('POST /api/ajout/fond', ['FondController', 'create']);

require_once __DIR__ . '/etudiants_routes.php';
require_once __DIR__ . '/status_pret_route.php';
require_once __DIR__ . '/type_pret_route.php';
require_once __DIR__ . '/enum_status_pret_route.php';
require_once __DIR__ . '/modalite_route.php';