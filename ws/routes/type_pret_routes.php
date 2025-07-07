<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

// Route pour servir la page HTML
Flight::route('GET /type-pret', function() {
    $htmlPath = __DIR__ . '/../../type-pret.html';
    if (file_exists($htmlPath)) {
        $content = file_get_contents($htmlPath);
        Flight::response()->header('Content-Type', 'text/html; charset=utf-8');
        echo $content;
    } else {
        Flight::notFound();
    }
});

// Routes API
Flight::route('GET /type-prets', ['TypePretController', 'getAll']);
Flight::route('GET /type-prets/@id', ['TypePretController', 'getById']);
Flight::route('POST /type-prets', ['TypePretController', 'create']);
Flight::route('PUT /type-prets/@id', ['TypePretController', 'update']);
Flight::route('DELETE /type-prets/@id', ['TypePretController', 'delete']);
