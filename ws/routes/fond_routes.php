<?php
require_once __DIR__ . '/../controllers/FondController.php';

Flight::route('POST /api/fond/ajout', ['FondController', 'create']);
Flight::route('GET /api/fond/', ['FondController', 'getAll']);