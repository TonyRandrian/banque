<?php
require_once __DIR__ . '/../controllers/PaiementModaliteController.php';

Flight::route('GET /paiement-modalites', ['PaiementModaliteController', 'getAll']);
Flight::route('GET /paiement-modalites/pret/@pret_id', ['PaiementModaliteController', 'getByPret']);
Flight::route('POST /paiement-modalites', ['PaiementModaliteController', 'create']);
Flight::route('DELETE /paiement-modalites/pret/@pret_id', ['PaiementModaliteController', 'deleteByPret']);
