<?php
    // Route d'accueil principal
require_once __DIR__ . '/../helpers/AppConfig.php';

Flight::route('GET /', function() {
    $data = [
        'pageTitle' => "Accueil - Banque Moderne",
        'page' => "accueil",
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'accueil'
    ];
    
    // Extraction des variables pour le template
    extract($data);
    include __DIR__ . '/../../templates.php';
});

?>