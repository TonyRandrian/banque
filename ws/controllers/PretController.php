<?php
require_once __DIR__ . '/../services/PretService.php';

class PretController {
    public static function getAll() {
        echo json_encode(PretService::getAll());
    }

    public static function getById($id) {
        echo json_encode(PretService::getById($id));
    }

    public static function create() {
        $data = Flight::request()->data->getData();
        echo json_encode(PretService::create($data));
    }

    public static function update($id) {
        $data = Flight::request()->data->getData();
        echo json_encode(PretService::update($id, $data));
    }

    public static function delete($id) {
        echo json_encode(PretService::delete($id));
    }

    public static function getModalites() {
        echo json_encode(PretService::getModalites());
    }

    public static function getTypePrets() {
        echo json_encode(PretService::getTypePrets());
    }
    /**
     * Web service pour les intérêts gagnés par mois (POST)
     * Attend : date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMois() {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;
        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMois($date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour les intérêts gagnés par mois pour tous les prêts (POST)
     * Attend : date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMoisTousPrets() {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;
        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMoisTousPrets($date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour les intérêts gagnés par mois pour un prêt spécifique (POST)
     * Attend : pret_id, date_debut=YYYY-MM&date_fin=YYYY-MM
     */
    public static function getInteretsParMoisPourPret($pret_id) {
        $data = Flight::request()->data;
        $date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
        $date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;
        if (!$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "date_debut et date_fin requis"]);
            return;
        }
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getInteretsParMoisPourPret($pret_id, $date_debut, $date_fin);
        echo json_encode($result);
    }

    /**
     * Web service pour obtenir la liste des prêts acceptés
     */
    public static function getPretsAcceptes() {
        require_once __DIR__ . '/../models/Pret.php';
        $result = Pret::getPretsAcceptes();
        echo json_encode($result);
    }
}
