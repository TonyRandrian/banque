<?php
require_once __DIR__ . '/../db.php';

class Fond
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM examS4_tresorerie ORDER BY date_mouvement DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM examS4_tresorerie WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getLastSolde()
    {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT solde FROM examS4_tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1");
            $solde = $stmt->fetchColumn();
            
            if ($solde === false || $solde === null) {
                return 0;
            }
            
            return floatval($solde);
            
        } catch (Exception $e) {
            error_log("Erreur dans getLastSolde: " . $e->getMessage());
            return 0;
        }
    }

    public static function getLastDate()
    {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT date_mouvement FROM examS4_tresorerie ORDER BY date_mouvement DESC, id DESC LIMIT 1");
            $date = $stmt->fetchColumn();
            return $date === false ? null : $date;
            
        } catch (Exception $e) {
            error_log("Erreur dans getLastDate: " . $e->getMessage());
            return null;
        }
    }

    public static function insertMouvement($montant, $date_creation)
    {
        $db = getDB();
        $lastSolde = self::getLastSoldeBeforeDate($date_creation);
        $lastDate = self::getLastDate();

        // Vérification de la date
        if ($lastDate !== null && $date_creation < $lastDate) {
            throw new Exception("La date du mouvement doit être supérieure ou égale à la dernière date de mouvement ($lastDate)");
        }

        $nouveauSolde = $lastSolde + floatval($montant);
        $stmt = $db->prepare("INSERT INTO examS4_tresorerie (date_mouvement, solde) VALUES (?, ?)");
        $stmt->execute([$date_creation, $nouveauSolde]);
        return $db->lastInsertId();
    }

    public static function create($data)
    {
        // $data doit contenir 'montant' et 'date_creation'
        return self::insertMouvement($data['montant'], $data['date_creation']);
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE examS4_tresorerie SET solde = ?, date_mouvement = ? WHERE id = ?");
        $stmt->execute([$data->montant, $data->date_creation, $id]);
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM examS4_tresorerie WHERE id = ?");
        $stmt->execute([$id]);
    }
   
    public static function getSoldeByMonth($dateDebut, $dateFin) {
        try {
            $moisData = [];
            
            $debut = new DateTime($dateDebut . '-01');
            $fin = new DateTime($dateFin . '-01');
            $fin->modify('last day of this month');
            
            $current = clone $debut;
            while ($current <= $fin) {
                $moisKey = $current->format('Y-m');
                $finMois = clone $current;
                $finMois->modify('last day of this month');
                
                $dernierSolde = self::getLastSoldeBeforeDate($finMois->format('Y-m-d'));
                
                $totalPaiements = PaiementModalite::getTotalByDate($finMois->format('Y-m-d'));
                
                $soldeTotal = $dernierSolde + $totalPaiements;
                
                $moisData[$moisKey] = $soldeTotal;
                
                $current->modify('+1 month');
            }
            
            return $moisData;
            
        } catch (Exception $e) {
            error_log("Erreur dans getSoldeByMonth: " . $e->getMessage());
            return [];
        }
    }

    public static function getSoldeForMonth($mois) {
        try {
            if (!preg_match('/^\d{4}-\d{2}$/', $mois)) {
                throw new Exception("Format de mois invalide. Utilisez YYYY-MM");
            }
            
            $finMois = new DateTime($mois . '-01');
            $finMois->modify('last day of this month');
            $dateFinMois = $finMois->format('Y-m-d');
            $dernierSolde = self::getLastSoldeBeforeDate($dateFinMois);
            $totalPaiements = PaiementModalite::getTotalByDate($dateFinMois);
            $dernierSolde = floatval($dernierSolde);
            $totalPaiements = floatval($totalPaiements);
            $soldeTotal = $dernierSolde + $totalPaiements;
            return $soldeTotal;
            
        } catch (Exception $e) {
            error_log("Erreur dans getSoldeForMonth: " . $e->getMessage());
            return 0.0;
        }
    }
    
  
    public static function getLastSoldeBeforeDate($date) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT solde FROM examS4_tresorerie WHERE date_mouvement <= ? ORDER BY date_mouvement DESC, id DESC LIMIT 1");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['solde'])) {
                return floatval($result['solde']);
            }
            return 0;
            
        } catch (Exception $e) {
            error_log("Erreur dans getLastSoldeBeforeDate: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function getStatistiques() {
        try {
            $soldeActuel = self::getLastSoldeBeforeDate(date('Y-m-d'));
            $totalPaiements = PaiementModalite::getTotalByDate(date('Y-m-d'));
            $soldeTotal = $soldeActuel + $totalPaiements;
            
            return [
                'solde_tresorerie' => $soldeActuel,
                'total_paiements' => $totalPaiements,
                'solde_total' => $soldeTotal,
                'derniere_mise_a_jour' => self::getLastDate() ?: 'Aucune'
            ];
            
        } catch (Exception $e) {
            error_log("Erreur dans getStatistiques: " . $e->getMessage());
            return [
                'solde_tresorerie' => 0,
                'total_paiements' => 0,
                'solde_total' => 0,
                'derniere_mise_a_jour' => 'Erreur'
            ];
        }
    }
}