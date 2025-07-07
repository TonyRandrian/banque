<?php
// Page d'accueil indépendante du site bancaire
require_once 'ws/helpers/AppConfig.php';

$pageTitle = 'Bienvenue - Banque Moderne';
$cssPath = AppConfig::getCssPath();
$sidebarItems = AppConfig::getSidebarItems();
$activePage = 'accueil'; // Marquer la page accueil comme active
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= $cssPath ?>">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>🏦 Banque</h3>
        </div>
        
        <ul class="list-unstyled components">
            <?php foreach ($sidebarItems as $item): ?>
                <?php 
                $isActive = isset($item['page']) && $item['page'] === $activePage;
                $activeClass = $isActive ? ' active' : '';
                ?>
                <li>
                    <a href="<?= htmlspecialchars($item['link']) ?>" class="<?= $activeClass ?>">
                        <?= $item['icon'] . ' ' . htmlspecialchars($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="sidebar-footer">
            <p>&copy; 2025 Système Bancaire</p>
        </div>
    </nav>

    <div class="content">
        <div class="welcome-container">
            <div class="welcome-left">
                <svg width="500" height="400" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="welcome-svg">
                    <rect x="100" y="150" width="300" height="200" fill="#2c5aa0" stroke="#1e3a8a" stroke-width="2"/>
                    <rect x="120" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="160" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="200" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="240" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="280" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="320" y="120" width="20" height="230" fill="#1e3a8a"/>
                    <rect x="360" y="120" width="20" height="230" fill="#1e3a8a"/>
                    
                    <!-- Toit/Fronton -->
                    <polygon points="80,120 250,80 420,120" fill="#1e3a8a"/>
                    
                    <!-- Texte BANQUE -->
                    <text x="250" y="110" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="20" font-weight="bold">BANQUE</text>
                    
                    <!-- Porte d'entrée -->
                    <rect x="230" y="280" width="40" height="70" fill="#374151"/>
                    <circle cx="260" cy="315" r="2" fill="#d1d5db"/>
                    
                    <!-- Fenêtres -->
                    <rect x="130" y="180" width="25" height="25" fill="#60a5fa"/>
                    <rect x="170" y="180" width="25" height="25" fill="#60a5fa"/>
                    <rect x="210" y="180" width="25" height="25" fill="#60a5fa"/>
                    <rect x="275" y="180" width="25" height="25" fill="#60a5fa"/>
                    <rect x="315" y="180" width="25" height="25" fill="#60a5fa"/>
                    <rect x="355" y="180" width="25" height="25" fill="#60a5fa"/>
                    
                    <rect x="130" y="220" width="25" height="25" fill="#60a5fa"/>
                    <rect x="170" y="220" width="25" height="25" fill="#60a5fa"/>
                    <rect x="210" y="220" width="25" height="25" fill="#60a5fa"/>
                    <rect x="275" y="220" width="25" height="25" fill="#60a5fa"/>
                    <rect x="315" y="220" width="25" height="25" fill="#60a5fa"/>
                    <rect x="355" y="220" width="25" height="25" fill="#60a5fa"/>
                    
                    <!-- Escaliers -->
                    <rect x="90" y="340" width="320" height="10" fill="#6b7280"/>
                    <rect x="100" y="330" width="300" height="10" fill="#9ca3af"/>
                    <rect x="110" y="320" width="280" height="10" fill="#d1d5db"/>
                    
                    <!-- Nuages -->
                    <circle cx="80" cy="50" r="15" fill="#e5e7eb"/>
                    <circle cx="95" cy="45" r="20" fill="#e5e7eb"/>
                    <circle cx="110" cy="50" r="15" fill="#e5e7eb"/>
                    
                    <circle cx="380" cy="40" r="12" fill="#e5e7eb"/>
                    <circle cx="395" cy="35" r="18" fill="#e5e7eb"/>
                    <circle cx="410" cy="40" r="12" fill="#e5e7eb"/>
                    
                    <!-- Soleil -->
                    <circle cx="420" cy="80" r="25" fill="#fbbf24"/>
                    <line x1="420" y1="35" x2="420" y2="45" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="420" y1="115" x2="420" y2="125" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="375" y1="80" x2="385" y2="80" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="455" y1="80" x2="465" y2="80" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="389" y1="49" x2="396" y2="56" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="444" y1="104" x2="451" y2="111" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="451" y1="49" x2="444" y2="56" stroke="#fbbf24" stroke-width="3"/>
                    <line x1="396" y1="104" x2="389" y2="111" stroke="#fbbf24" stroke-width="3"/>
                </svg>
            </div>
            
            <div class="welcome-right">
                <div class="welcome-content">
                    <h1 class="welcome-title">Bienvenue dans votre Banque Moderne</h1>
                    <p class="welcome-subtitle">Votre partenaire de confiance pour tous vos besoins financiers</p>
                    
                    <div class="welcome-features">
                        <div class="feature-item">
                            <div class="feature-icon">💰</div>
                            <div class="feature-text">
                                <h3>Prêts Personnalisés</h3>
                                <p>Des solutions de financement adaptées à vos projets</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-text">
                                <h3>Gestion des Fonds</h3>
                                <p>Optimisez vos investissements avec nos experts</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🔒</div>
                            <div class="feature-text">
                                <h3>Sécurité Maximale</h3>
                                <p>Vos données protégées par les dernières technologies</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="welcome-actions">
                        <a href="/banque/ws/" class="btn btn-primary btn-lg">Accéder au Dashboard</a>
                        <a href="/banque/ws/type-pret" class="btn btn-outline-primary btn-lg">Voir nos Prêts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
