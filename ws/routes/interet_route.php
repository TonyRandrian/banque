<?php
require_once __DIR__ . '/../controllers/PretController.php';

// Route pour les intérêts gagnés par mois pour tous les prêts (POST)
Flight::route('POST /api/interets/mois', ['PretController', 'getInteretsParMoisTousPrets']);

// Route pour les intérêts gagnés par mois pour un prêt spécifique (POST)
Flight::route('POST /api/interets/mois/@pret_id', ['PretController', 'getInteretsParMoisPourPret']);

// Route pour obtenir la liste des prêts acceptés (GET)
Flight::route('GET /api/prets/acceptes', ['PretController', 'getPretsAcceptes']);

Flight::route('GET /interets', function() {
    $data = [
        'pageTitle' => "Stats intérêts - Banque Moderne",
        'page' => 'dashboard-interets',
        'sidebarItems' => AppConfig::getSidebarItems(),
        'cssPath' => AppConfig::getCssPath(),
        'activePage' => 'dashboard-interets'
    ];

    extract($data);
    include __DIR__ . '/../../templates.php';
});