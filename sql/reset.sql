-- Script de réinitialisation complète de la base de données
-- Supprime toutes les données et remet les auto_increment à 1

-- Désactiver les contraintes de clés étrangères temporairement
SET FOREIGN_KEY_CHECKS = 0;

-- Vider toutes les tables dans l'ordre inverse des dépendances
TRUNCATE TABLE simulation_paiement_modalite;
TRUNCATE TABLE paiement_modalite;
TRUNCATE TABLE status_pret;
TRUNCATE TABLE pret;
TRUNCATE TABLE simulation_pret;
TRUNCATE TABLE compte_client;
TRUNCATE TABLE client;
TRUNCATE TABLE tresorerie;
TRUNCATE TABLE config;
TRUNCATE TABLE modalite;
TRUNCATE TABLE type_pret;
TRUNCATE TABLE enum_status_pret;

-- Réinitialiser les auto_increment à 1 pour toutes les tables
ALTER TABLE enum_status_pret
    AUTO_INCREMENT = 1;
ALTER TABLE type_pret
    AUTO_INCREMENT = 1;
ALTER TABLE modalite
    AUTO_INCREMENT = 1;
ALTER TABLE client
    AUTO_INCREMENT = 1;
ALTER TABLE config
    AUTO_INCREMENT = 1;
ALTER TABLE tresorerie
    AUTO_INCREMENT = 1;
ALTER TABLE compte_client
    AUTO_INCREMENT = 1;
ALTER TABLE pret
    AUTO_INCREMENT = 1;
ALTER TABLE simulation_pret
    AUTO_INCREMENT = 1;
ALTER TABLE status_pret
    AUTO_INCREMENT = 1;
ALTER TABLE paiement_modalite
    AUTO_INCREMENT = 1;
ALTER TABLE simulation_paiement_modalite
    AUTO_INCREMENT = 1;

-- Réactiver les contraintes de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- Insérer des données de base nécessaires au fonctionnement

-- Statuts de prêt
INSERT INTO enum_status_pret (libelle)
VALUES ('En attente'),
       ('Approuvé'),
       ('Rejeté'),
       ('En cours'),
       ('Terminé'),
       ('Suspendu');

-- Types de prêt avec taux d'intérêt
INSERT INTO type_pret (libelle, taux, delai_debut_remboursement)
VALUES ('Prêt Immobilier', 3.50, 30),
       ('Prêt Automobile', 4.80, 0),
       ('Prêt Personnel', 6.20, 0),
       ('Prêt Étudiant', 2.80, 12),
       ('Prêt Travaux', 5.10, 0);

-- Modalités de remboursement
INSERT INTO modalite (libelle, nb_mois)
VALUES ('Mensuel', 1),
       ('Trimestriel', 3),
       ('Semestriel', 6),
       ('Annuel', 12);

-- Configuration système
INSERT INTO config (libelle, date_application, valeur)
VALUES ('Taux de base banque', '2024-01-01', '1.50'),
       ('Frais de dossier', '2024-01-01', '150.00'),
       ('Montant minimum prêt', '2024-01-01', '1000.00'),
       ('Montant maximum prêt', '2024-01-01', '500000.00');

-- Solde initial de la trésorerie
INSERT INTO tresorerie (date_mouvement, solde)
VALUES ('2024-01-01', 1000000.00);

-- Clients de test
INSERT INTO client (email, prenom, nom, mdp)
VALUES ('jean.dupont@email.com', 'Jean', 'Dupont', 'password123'),
       ('marie.martin@email.com', 'Marie', 'Martin', 'password456'),
       ('pierre.bernard@email.com', 'Pierre', 'Bernard', 'password789');

-- Comptes clients
INSERT INTO compte_client (numero, date_creation, client_id)
VALUES ('CC001-2024-001', '2024-01-15', 1),
       ('CC001-2024-002', '2024-01-20', 2),
       ('CC001-2024-003', '2024-02-01', 3);

-- Simulations de prêt pour tester la comparaison
INSERT INTO simulation_pret (duree_remboursement, montant, date_demande, modalite_id, type_pret_id, taux_assurance,
                             assurance_par_mois)
VALUES (24, 25000.00, '2024-01-15', 1, 2, 0.25, TRUE),  -- Simulation 1: Prêt auto 24 mois
       (36, 15000.00, '2024-02-01', 1, 3, 0.00, FALSE), -- Simulation 2: Prêt personnel 36 mois
       (12, 10000.00, '2024-02-15', 1, 3, 0.50, TRUE),  -- Simulation 3: Prêt personnel 12 mois
       (60, 200000.00, '2024-03-01', 1, 1, 0.15, TRUE);
-- Simulation 4: Prêt immobilier 60 mois

-- Échéancier pour la simulation 1 : 25000€, 4.80% annuel (0.4% mensuel), 24 mois
-- Mensualité = 1090.05€, Assurance = 62.50€/mois (0.25% de 25000)
INSERT INTO simulation_paiement_modalite (date_prevu_paiment, montant_prevu, mensualite, interet, amortissement,
                                          assurance, montant_restant, simulation_pret_id)
VALUES ('2024-02-15', 1152.55, 1090.05, 100.00, 990.05, 62.50, 24009.95, 1),
       ('2024-03-15', 1152.55, 1090.05, 96.04, 994.01, 62.50, 23015.94, 1),
       ('2024-04-15', 1152.55, 1090.05, 92.06, 997.99, 62.50, 22017.95, 1),
       ('2024-05-15', 1152.55, 1090.05, 88.07, 1001.98, 62.50, 21015.97, 1),
       ('2024-06-15', 1152.55, 1090.05, 84.06, 1005.99, 62.50, 20009.98, 1),
       ('2024-07-15', 1152.55, 1090.05, 80.04, 1010.01, 62.50, 18999.97, 1);

-- Échéancier pour la simulation 2 : 15000€, 6.20% annuel (0.517% mensuel), 36 mois
-- Mensualité = 456.33€, Pas d'assurance
INSERT INTO simulation_paiement_modalite (date_prevu_paiment, montant_prevu, mensualite, interet, amortissement,
                                          assurance, montant_restant, simulation_pret_id)
VALUES ('2024-03-01', 456.33, 456.33, 77.50, 378.83, 0.00, 14621.17, 2),
       ('2024-04-01', 456.33, 456.33, 75.54, 380.79, 0.00, 14240.38, 2),
       ('2024-05-01', 456.33, 456.33, 73.58, 382.75, 0.00, 13857.63, 2),
       ('2024-06-01', 456.33, 456.33, 71.61, 384.72, 0.00, 13472.91, 2),
       ('2024-07-01', 456.33, 456.33, 69.64, 386.69, 0.00, 13086.22, 2);

-- Échéancier pour la simulation 3 : 10000€, 6.20% annuel (0.517% mensuel), 12 mois
-- Mensualité = 860.66€, Assurance = 50€/mois (0.50% de 10000)
INSERT INTO simulation_paiement_modalite (date_prevu_paiment, montant_prevu, mensualite, interet, amortissement,
                                          assurance, montant_restant, simulation_pret_id)
VALUES ('2024-03-15', 910.66, 860.66, 51.70, 808.96, 50.00, 9191.04, 3),
       ('2024-04-15', 910.66, 860.66, 47.52, 813.14, 50.00, 8377.90, 3),
       ('2024-05-15', 910.66, 860.66, 43.32, 817.34, 50.00, 7560.56, 3),
       ('2024-06-15', 910.66, 860.66, 39.09, 821.57, 50.00, 6738.99, 3);

COMMIT;

-- Afficher un résumé de la réinitialisation
SELECT 'Base de données réinitialisée avec succès!' as Status;
SELECT 'enum_status_pret'                                                    as Table_Name,
       COUNT(*)                                                              as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enum_status_pret') as Next_Auto_Increment
FROM enum_status_pret
UNION ALL
SELECT 'type_pret'                                                    as Table_Name,
       COUNT(*)                                                       as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'type_pret') as Next_Auto_Increment
FROM type_pret
UNION ALL
SELECT 'modalite'                                                    as Table_Name,
       COUNT(*)                                                      as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'modalite') as Next_Auto_Increment
FROM modalite
UNION ALL
SELECT 'client'                                                    as Table_Name,
       COUNT(*)                                                    as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'client') as Next_Auto_Increment
FROM client
UNION ALL
SELECT 'simulation_pret'                                                    as Table_Name,
       COUNT(*)                                                             as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'simulation_pret') as Next_Auto_Increment
FROM simulation_pret
UNION ALL
SELECT 'simulation_paiement_modalite'                      as Table_Name,
       COUNT(*)                                            as Records_Count,
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'simulation_paiement_modalite') as Next_Auto_Increment
FROM simulation_paiement_modalite;