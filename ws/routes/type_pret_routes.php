<?php
require_once __DIR__ . '/../controllers/TypePretController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';
require_once __DIR__ . '/../controllers/TypePretController.php';

Flight::route('GET /type_pret', ['TypePretController', 'getAll']);

// Route pour servir la page HTML
Flight::route('GET /type-pret', function() {
    $data = [
        'pageTitle' => "Gestion des Types de Prêt - Banque Moderne",
        'page' => "type-pret",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'type-pret'
    ];
    
    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

// Routes API
Flight::route('GET /type-prets', ['TypePretController', 'getAll']);
Flight::route('GET /type-prets/@id', ['TypePretController', 'getById']);
Flight::route('POST /type-prets', ['TypePretController', 'create']);
Flight::route('PUT /type-prets/@id', ['TypePretController', 'update']);
Flight::route('DELETE /type-prets/@id', ['TypePretController', 'delete']);
