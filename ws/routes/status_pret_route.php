<?php
require_once __DIR__ . '/../controllers/StatusPretController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';


// Routes alternatives avec tirets pour correspondre aux URLs utilisées dans les pages
Flight::route('GET /status-prets', ['StatusPretController', 'getAll']);
Flight::route('GET /status-prets/@id', ['StatusPretController', 'getById']);
Flight::route('POST /status-prets', ['StatusPretController', 'create']);
Flight::route('PUT /status-prets/@id', ['StatusPretController', 'update']);
Flight::route('DELETE /status-prets/@id', ['StatusPretController', 'delete']);


// Route pour la page statuts de prêts
Flight::route('GET /status-pret', function() {
    $data = [
        'pageTitle' => "Gestion des Statuts de Prêt - Banque Moderne",
        'page' => "status-pret",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'status-pret'
    ];
    
    extract($data);
    include __DIR__ . '/../../templates.php';
});

