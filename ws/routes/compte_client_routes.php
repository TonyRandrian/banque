<?php
require_once __DIR__ . '/../controllers/CompteClientController.php';

// API comptes clients
Flight::route('GET /comptes-clients', ['CompteClientController', 'getAll']);
Flight::route('GET /comptes-clients/@id', ['CompteClientController', 'getById']);
Flight::route('POST /comptes-clients', ['CompteClientController', 'create']);
Flight::route('PUT /comptes-clients/@id', ['CompteClientController', 'update']);
Flight::route('DELETE /comptes-clients/@id', ['CompteClientController', 'delete']);
