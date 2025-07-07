<?php
require_once __DIR__ . '/../controllers/FondController.php';

Flight::route('POST /api/ajout/fond', ['FondController', 'create']);
Flight::route('POST /api/')