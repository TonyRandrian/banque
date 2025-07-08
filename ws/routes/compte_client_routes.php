<?php
require_once __DIR__ . '/../controllers/CompteClientController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';

// Route pour servir la page HTML
Flight::route('GET /compte-client', function() {
    $data = [
        'pageTitle' => "Création de Comptes Clients - Banque Moderne",
        'page' => "compte-client",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'compte-client'
    ];
    
    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

// Routes API
Flight::route('GET /compte-clients', ['CompteClientController', 'getAll']);
Flight::route('GET /compte-clients/@id', ['CompteClientController', 'getById']);
Flight::route('POST /compte-clients', ['CompteClientController', 'create']);
Flight::route('PUT /compte-clients/@id', ['CompteClientController', 'update']);
Flight::route('DELETE /compte-clients/@id', ['CompteClientController', 'delete']);

