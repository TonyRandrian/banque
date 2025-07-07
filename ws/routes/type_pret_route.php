<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

Flight::route('GET /type_pret', ['TypePretController', 'getAll']);
