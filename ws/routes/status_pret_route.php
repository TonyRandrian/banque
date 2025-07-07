<?php
require_once __DIR__ . '/../controllers/StatusPretController.php';

Flight::route('GET /status_prets', ['StatusPretController', 'getAll']);
Flight::route('GET /status_prets/@id', ['StatusPretController', 'getById']);
Flight::route('POST /status_prets', ['StatusPretController', 'create']);
Flight::route('PUT /status_prets/@id', ['StatusPretController', 'update']);
Flight::route('DELETE /status_prets/@id', ['StatusPretController', 'delete']);
