<?php
require_once __DIR__ . '/../db.php';

class CompteClient {
    public static function getAll() {
        $db = getDB();
        $sql = "SELECT cc.*, c.nom, c.prenom, c.email 
                FROM examS4_compte_client cc
                JOIN examS4_client c ON cc.client_id = c.id
                ORDER BY cc.id DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $sql = "SELECT cc.*, c.nom, c.prenom, c.email 
                FROM examS4_compte_client cc
                JOIN examS4_client c ON cc.client_id = c.id
                WHERE cc.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // Créer d'abord le client
            $clientStmt = $db->prepare("INSERT INTO examS4_client (nom, prenom, email, mdp) VALUES (?, ?, ?, ?)");
            $clientStmt->execute([
                $data['nom'],
                $data['prenom'], 
                $data['email'],
                password_hash($data['mdp'], PASSWORD_DEFAULT)
            ]);
            
            $clientId = $db->lastInsertId();
            
            // Générer un numéro de compte unique
            $numeroCompte = 'CL' . str_pad($clientId, 8, '0', STR_PAD_LEFT);
            
            // Créer le compte client
            $compteStmt = $db->prepare("INSERT INTO examS4_compte_client (numero, date_creation, client_id) VALUES (?, ?, ?)");
            $compteStmt->execute([
                $numeroCompte,
                $data['date_creation'],
                $clientId
            ]);
            
            $compteId = $db->lastInsertId();
            
            $db->commit();
            
            return ['compte_id' => $compteId, 'client_id' => $clientId, 'numero' => $numeroCompte];
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function update($id, $data) {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // Récupérer l'ID du client associé au compte
            $stmt = $db->prepare("SELECT client_id FROM examS4_compte_client WHERE id = ?");
            $stmt->execute([$id]);
            $compte = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$compte) {
                throw new Exception("Compte non trouvé");
            }
            
            // Mettre à jour les informations du client
            $clientStmt = $db->prepare("UPDATE examS4_client SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $clientStmt->execute([
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $compte['client_id']
            ]);
            
            // Mettre à jour le mot de passe seulement s'il est fourni
            if (!empty($data['mdp'])) {
                $mdpStmt = $db->prepare("UPDATE examS4_client SET mdp = ? WHERE id = ?");
                $mdpStmt->execute([
                    password_hash($data['mdp'], PASSWORD_DEFAULT),
                    $compte['client_id']
                ]);
            }
            
            // Mettre à jour la date de création du compte si fournie
            if (!empty($data['date_creation'])) {
                $compteStmt = $db->prepare("UPDATE examS4_compte_client SET date_creation = ? WHERE id = ?");
                $compteStmt->execute([$data['date_creation'], $id]);
            }
            
            $db->commit();
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function delete($id) {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // Récupérer l'ID du client associé au compte
            $stmt = $db->prepare("SELECT client_id FROM examS4_compte_client WHERE id = ?");
            $stmt->execute([$id]);
            $compte = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$compte) {
                throw new Exception("Compte non trouvé");
            }
            
            // Supprimer le compte client
            $compteStmt = $db->prepare("DELETE FROM examS4_compte_client WHERE id = ?");
            $compteStmt->execute([$id]);
            
            // Supprimer le client
            $clientStmt = $db->prepare("DELETE FROM examS4_client WHERE id = ?");
            $clientStmt->execute([$compte['client_id']]);
            
            $db->commit();
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
