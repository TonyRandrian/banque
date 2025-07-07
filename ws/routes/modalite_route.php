<?php
require_once __DIR__ . '/../controllers/ModaliteController.php';

Flight::route('GET /modalite', ['ModaliteController', 'getAll']);
