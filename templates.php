<?php
// Les variables $pageTitle, $page, $sidebarItems sont envoyées par les routes via Flight::render()
// Si elles ne sont pas définies (cas du index.php), on utilise AppConfig
if (!isset($pageTitle)) {
    if (!class_exists('AppConfig')) {
        require_once 'ws/helpers/AppConfig.php';
    }
    $currentPage = AppConfig::getCurrentPage();
    $pageConfig = AppConfig::getPageConfig($currentPage);
    $pageTitle = $pageConfig['title'];
    $page = $pageConfig['content'];
    $sidebarItems = AppConfig::getSidebarItems();
}

if (!isset($cssPath)) {
    $cssPath = AppConfig::getCssPath();
}

// Déterminer la page active pour le menu
if (!isset($activePage)) {
    $activePage = isset($page) ? $page : AppConfig::getActivePage();
}
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

    <!-- Main Content -->
    <div class="content">
        <?php 
        if (isset($page) && !empty($page)) {
            // Chercher le fichier à la racine du projet
            $pageFile = __DIR__ . "/" . $page . ".php";
            if (file_exists($pageFile)) {
                include $pageFile;
            } else {
                echo "<div class='alert alert-danger'>Page non trouvée : " . htmlspecialchars($page) . " (cherché dans : " . $pageFile . ")</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Aucune page spécifiée</div>";
        }
        ?>
    </div>
</body>
</html>
