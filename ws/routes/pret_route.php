<?php
require_once __DIR__ . '/../controllers/PretController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';

Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('GET /prets/valide', ['PretController', 'getValide']);
Flight::route('GET /prets/@id', ['PretController', 'getById']);
Flight::route('POST /prets', ['PretController', 'create']);
Flight::route('PUT /prets/@id', ['PretController', 'update']);
Flight::route('DELETE /prets/@id', ['PretController', 'delete']);


// Route pour la page prêts
Flight::route('GET /pret', function() {
    $data = [
        'pageTitle' => "Gestion des Prêts - Banque Moderne",
        'page' => "pret",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'pret'
    ];
    
    extract($data);
    include __DIR__ . '/../../templates.php';
});
