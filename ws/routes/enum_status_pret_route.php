<?php
require_once __DIR__ . '/../controllers/EnumStatusPretController.php';

Flight::route('GET /enum-status-prets', ['EnumStatusPretController', 'getAll']);
