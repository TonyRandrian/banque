<?php
require_once __DIR__ . '/../controllers/FondController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';

// Route pour servir la page HTML
Flight::route('GET /fond', function() {
    $data = [
        'pageTitle' => "Gestion des Fonds - Banque Moderne",
        'page' => "fond", 
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'fond'
    ];
    
    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

Flight::route('POST /api/fond/ajout', ['FondController', 'create']);
Flight::route('GET /api/fond/', ['FondController', 'getAll']);