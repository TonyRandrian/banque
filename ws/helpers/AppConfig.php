<?php
class AppConfig 
{
    public static function getRoutes() 
    {
        return [
            'accueil' => [
                'title' => 'Accueil - Banque Moderne',
                'content' => 'accueil'
            ],
            'type-pret' => [
                'title' => 'Gestion des Types de Prêt - Banque Moderne',
                'content' => 'type-pret'
            ],
            'fond' => [
                'title' => 'Gestion des Fonds - Banque Moderne', 
                'content' => 'fond'
            ],
            'pret' => [
                'title' => 'Gestion des Prêts - Banque Moderne',
                'content' => 'pret'
            ],
            'status-pret' => [
                'title' => 'Gestion des Statuts de Prêt - Banque Moderne',
                'content' => 'status-pret'
            ],
            'compte-client' => [
                'title' => 'Création de Comptes Clients - Banque Moderne',
                'content' => 'compte-client'
            ]
        ];
    }
    
    public static function getSidebarItems() 
    {
        return [
            [
                'label' => 'Accueil',
                'link' => '/banque',
                'icon' => '🏠',
                'page' => 'accueil'
            ],
            [
                'label' => 'Types de Prêts',
                'link' => '/banque/ws/type-pret',
                'icon' => '💰',
                'page' => 'type-pret'
            ],
            [
                'label' => 'Gestion des Fonds',
                'link' => '/banque/ws/fond',
                'icon' => '📊',
                'page' => 'fond'
            ],
            [
                'label' => 'Prêts',
                'link' => '/banque/ws/pret',
                'icon' => '🏦',
                'page' => 'pret'
            ],
            [
                'label' => 'Statuts de Prêts',
                'link' => '/banque/ws/status-pret',
                'icon' => '📋',
                'page' => 'status-pret'
            ],
            [
                'label' => 'Comptes Clients',
                'link' => '/banque/ws/compte-client',
                'icon' => '👥',
                'page' => 'compte-client'
            ]
        ];
    }
    
    public static function getCurrentPage() 
    {
        return $_GET['page'] ?? 'accueil';
    }
    
    public static function getPageConfig($page) 
    {
        $routes = self::getRoutes();
        return $routes[$page] ?? $routes['accueil'];
    }
    
    public static function getCssPath() 
    {
        $baseDir = '/banque';
        return $baseDir . '/assets/css/style.css';
    }
    
    public static function getBasePath() 
    {
        return '/banque'; 
    }
 
    public static function getActivePage() 
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rtrim($path, '/');
        
        // Détection de la page active
        if (strpos($path, '/type-pret') !== false) {
            return 'type-pret';
        } elseif (strpos($path, '/fond') !== false) {
            return 'fond';
        } elseif (strpos($path, '/pret') !== false && strpos($path, '/status-pret') === false) {
            return 'pret';
        } elseif (strpos($path, '/status-pret') !== false) {
            return 'status-pret';
        } elseif (isset($_GET['page'])) {
            return $_GET['page'];
        } elseif ($path === '/banque/ws' || $path === '/banque' || $path === '' || $path === '/banque/index.php') {
            return 'accueil';
        } else {
            return 'accueil';
        }
    }
}
