<?php
require_once __DIR__ . '/../models/Fond.php';
require_once __DIR__ . '/../models/PaiementModalite.php';

class DashboardFondController
{
   
    public static function getSoldeByMonth() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            if (empty($data['date_debut']) || empty($data['date_fin'])) {
                Flight::json(['error' => 'Les paramètres date_debut et date_fin sont requis'], 400);
                return;
            }
            if (!preg_match('/^\d{4}-\d{2}$/', $data['date_debut']) || !preg_match('/^\d{4}-\d{2}$/', $data['date_fin'])) {
                Flight::json(['error' => 'Format de date invalide. Utilisez YYYY-MM'], 400);
                return;
            }
            if ($data['date_debut'] > $data['date_fin']) {
                Flight::json(['error' => 'La date de début doit être antérieure ou égale à la date de fin'], 400);
                return;
            }
            $result = Fond::getSoldeByMonth($data['date_debut'], $data['date_fin']);
            Flight::json($result);
            
        } catch (Exception $e) {
            error_log("Erreur dans getSoldeByMonth: " . $e->getMessage());
            Flight::json(['error' => 'Erreur lors du calcul des soldes: ' . $e->getMessage()], 500);
        }
    }

    public static function getStatistiques() {
        try {
            $stats = Fond::getStatistiques();
            Flight::json($stats);
            
        } catch (Exception $e) {
            error_log("Erreur dans getStatistiques: " . $e->getMessage());
            Flight::json(['error' => 'Erreur lors du calcul des statistiques'], 500);
        }
    }
}
