<?php
require_once __DIR__ . '/../db.php';

class SimulationPret
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM simulation_pret ORDER BY date_demande DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM simulation_pret WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getStatistiques($id)
    {
        $db = getDB();
        
        // Récupérer les informations de base de la simulation
        $simulation = self::findById($id);
        if (!$simulation) {
            return null;
        }

        // Récupérer tous les paiements de modalité pour cette simulation
        $stmt = $db->prepare("
            SELECT * FROM simulation_paiement_modalite 
            WHERE simulation_pret_id = ? 
            ORDER BY date_prevu_paiment ASC
        ");
        $stmt->execute([$id]);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculer les statistiques
        $totalInteret = 0;
        $sommeMensualites = 0;
        foreach ($paiements as $paiement) {
            $totalInteret += $paiement['interet'];
            $sommeMensualites += $paiement['mensualite'];
        }

        $dureePret = $simulation['duree_remboursement'];
        $interetMoyenMois = $dureePret > 0 ? $totalInteret / $dureePret : 0;

        return [
            'total_interet' => $totalInteret,
            'duree_pret' => $dureePret,
            'interet_moyen_mois' => $interetMoyenMois,
            'somme_mensualites' => $sommeMensualites,
            'montant' => isset($simulation['montant']) ? $simulation['montant'] : null,
            'echeancier' => $paiements
        ];
    }

    public static function compareTwo($id1, $id2)
    {
        $stats1 = self::getStatistiques($id1);
        $stats2 = self::getStatistiques($id2);

        if (!$stats1 || !$stats2) {
            return null;
        }

        return [
            'simulation1' => [
                'id' => $id1,
                'stats' => [
                    'total_interet' => $stats1['total_interet'],
                    'duree_pret' => $stats1['duree_pret'],
                    'interet_moyen_mois' => $stats1['interet_moyen_mois'],
                    'somme_mensualites' => $stats1['somme_mensualites'],
                    'montant' => $stats1['montant']
                ],
                'echeancier' => $stats1['echeancier']
            ],
            'simulation2' => [
                'id' => $id2,
                'stats' => [
                    'total_interet' => $stats2['total_interet'],
                    'duree_pret' => $stats2['duree_pret'],
                    'interet_moyen_mois' => $stats2['interet_moyen_mois'],
                    'somme_mensualites' => $stats2['somme_mensualites'],
                    'montant' => $stats2['montant']
                ],
                'echeancier' => $stats2['echeancier']
            ]
        ];
    }
}