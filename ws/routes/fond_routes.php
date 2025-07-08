<?php
require_once __DIR__ . '/../controllers/FondController.php';
require_once __DIR__ . '/../controllers/DashboardFondController.php';
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

// Route pour servir la page HTML
Flight::route('GET /dashboard', function() {
    $data = [
        'pageTitle' => "Dashboard de fond",
        'page' => "dashboard-fond", 
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'dashboard-fond'
    ];
    
    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

// Route pour l'API dashboard fonds
Flight::route('POST /api/fonds/mois', ['DashboardFondController', 'getSoldeByMonth']);

// Route pour les statistiques des fonds
Flight::route('GET /api/fonds/stats', ['DashboardFondController', 'getStatistiques']);

Flight::route('POST /api/fond/ajout', ['FondController', 'create']);
Flight::route('GET /api/fond/', ['FondController', 'getAll']);