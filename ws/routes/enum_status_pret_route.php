<?php
require_once __DIR__ . '/../controllers/EnumStatusPretController.php';

Flight::route('GET /enum_status_prets', ['EnumStatusPretController', 'getAll']);
