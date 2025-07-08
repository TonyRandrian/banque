<?php
// Include all route files
require_once 'type_pret_routes.php';
require_once 'fond_routes.php';
require_once 'pret_route.php'; 
require_once 'simulation_pret_route.php'; 
require_once 'enum_status_pret_route.php'; 
require_once 'modalite_route.php';
require_once 'status_pret_route.php';
require_once 'compte_client_routes.php';
require_once 'paiement_modalite_routes.php';
require_once 'simulation_paiement_modalite_routes.php';
require_once 'compte_client_routes.php';

// Routes pour les pages web avec template
require_once __DIR__ . '/../helpers/AppConfig.php';
Flight::route('POST /api/ajout/fond', ['FondController', 'create']);

require_once __DIR__ . '/status_pret_route.php';
require_once __DIR__ . '/type_pret_routes.php';
require_once __DIR__ . '/enum_status_pret_route.php';
require_once __DIR__ . '/modalite_route.php';
require_once __DIR__ . '/interet_route.php';

// Route pour la page simulation de prêt
Flight::route('GET /simulation-pret', function() {
    $data = [
        'pageTitle' => "Simulation de Prêt - Banque Moderne",
        'page' => "simulation_pret",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'simulation-pret'
    ];
    extract($data);
    include __DIR__ . '/../../templates.php';
});
