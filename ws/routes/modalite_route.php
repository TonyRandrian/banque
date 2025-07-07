<?php
require_once __DIR__ . '/../controllers/ModaliteController.php';

Flight::route('GET /modalites', ['ModaliteController', 'getAll']);
