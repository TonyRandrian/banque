<?php
require_once __DIR__ . '/../controllers/FondController.php';

Flight::route('POST /api/ajout/fond', ['FondController', 'create']);

require_once 'etudiants_routes.php';
require_once 'status_pret_route.php';
require_once 'type_pret_route.php';
require_once 'enum_status_pret_route.php';
require_once 'modalite_route.php';
require_once 'pret_route.php';