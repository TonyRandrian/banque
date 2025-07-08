<?php
require_once __DIR__ . '/../controllers/SimulationController.php';
require_once __DIR__ . '/../helpers/AppConfig.php';

Flight::route('GET /comparaison', function () {
    $data = [
        'pageTitle' => "Comparaison - Banque Moderne",
        'page' => "comparaison",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'comparaison',
    ];

    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

Flight::route('GET /comparaisons', ['SimulationController', 'getAll']);
Flight::route('GET /comparaison/@id1/@id2', ['SimulationController', 'getTwo']);