-- Types de prêt
insert into type_pret(libelle, taux)
values ('type 1', 144.0),
       ('type 2', 8.00);

-- Modalités
insert into modalite(libelle, nb_mois)
values ('Mensuel', 1);

-- Statuts prêt
insert into enum_status_pret(libelle)
values ('en attente'),
       ('accepté'),
       ('refusé'),
       ('en cours de traitement'),
       ('terminé');

-- Clients de test
insert into client(email, prenom, nom, mdp)
values ('client1@test.com', 'Jean', 'Dupont', 'password123'),
       ('client2@test.com', 'Marie', 'Martin', 'password456');

-- Comptes clients
insert into compte_client(numero, date_creation, client_id)
values ('CPT001', '2024-01-01', 1),
       ('CPT002', '2024-01-15', 2);

-- Prêts
insert into pret(duree_remboursement, montant, date_demande, modalite_id, type_pret_id, compte_client_id)
values (12, 6000, '2024-01-10', 1, 1, 1),
       (24, 48000, '2024-02-01', 1, 2, 2);

-- Statuts des prêts (acceptés)
insert into status_pret(date_status, enum_pret_id, pret_id)
values ('2024-01-15', 2, 1),
       ('2024-02-10', 2, 2);

-- 12 échéances mensuelles pour le prêt 1 (déjà présentes)
insert into paiement_modalite(date_prevu_paiment, montant_prevu, interet, amortissement, pret_id)
values ('2024-02-10', 968.62, 720, 248.62, 1),
       ('2024-03-10', 968.62, 690.17, 278.45, 1),
       ('2024-04-10', 968.62, 656.75, 311.87, 1),
       ('2024-05-10', 968.62, 619.33, 349.29, 1),
       ('2024-06-10', 968.62, 577.41, 391.20, 1),
       ('2024-07-10', 968.62, 530.47, 438.15, 1),
       ('2024-08-10', 968.62, 483.52, 485.1, 1),
       ('2024-09-10', 968.62, 425.31, 543.31, 1),
       ('2024-10-10', 968.62, 360.11, 608.51, 1),
       ('2024-11-10', 968.62, 287.09, 681.53, 1),
       ('2024-12-10', 968.62, 205.30, 763.32, 1),
       ('2025-01-10', 968.62, 113.70, 854.92, 1);

-- 24 échéances mensuelles pour le prêt 2 (calculées pour 48000€, 8% annuel, 24 mois)
insert into paiement_modalite(date_prevu_paiment, montant_prevu, interet, amortissement, pret_id)
values ('2024-03-01', 2172.57, 320.00, 1852.57, 2),
       ('2024-04-01', 2172.57, 307.65, 1864.92, 2),
       ('2024-05-01', 2172.57, 295.22, 1877.35, 2),
       ('2024-06-01', 2172.57, 282.70, 1889.87, 2),
       ('2024-07-01', 2172.57, 270.10, 1902.47, 2),
       ('2024-08-01', 2172.57, 257.41, 1915.16, 2),
       ('2024-09-01', 2172.57, 244.63, 1927.94, 2),
       ('2024-10-01', 2172.57, 231.77, 1940.80, 2),
       ('2024-11-01', 2172.57, 218.82, 1953.75, 2),
       ('2024-12-01', 2172.57, 205.78, 1966.79, 2),
       ('2025-01-01', 2172.57, 192.67, 1979.90, 2),
       ('2025-02-01', 2172.57, 179.47, 1993.10, 2),
       ('2025-03-01', 2172.57, 166.19, 2006.38, 2),
       ('2025-04-01', 2172.57, 152.82, 2019.75, 2),
       ('2025-05-01', 2172.57, 139.37, 2033.20, 2),
       ('2025-06-01', 2172.57, 125.84, 2046.73, 2),
       ('2025-07-01', 2172.57, 112.23, 2060.34, 2),
       ('2025-08-01', 2172.57, 98.54, 2074.03, 2),
       ('2025-09-01', 2172.57, 84.77, 2087.80, 2),
       ('2025-10-01', 2172.57, 70.92, 2101.65, 2),
       ('2025-11-01', 2172.57, 56.99, 2115.58, 2),
       ('2025-12-01', 2172.57, 42.98, 2129.59, 2),
       ('2026-01-01', 2172.57, 28.89, 2143.68, 2),
       ('2026-02-01', 2172.57, 14.72, 2157.85, 2),
       ('2026-03-01', 2172.57, 0.47, 2172.10, 2);

-- Employés pour les tests
insert into employe(nom, prenom, email, mdp)
values ("Dupont", "Jean", "jean.dupont@banque.fr", "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"),
       ("Martin", "Marie", "marie.martin@banque.fr", "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi");
-- Jeu de données pour tester la simulation de prêt

-- Clients
INSERT INTO client (email, prenom, mdp, nom)
VALUES ('alice@example.com', 'Alice', 'pass1', 'Durand'),
       ('bob@example.com', 'Bob', 'pass2', 'Martin');

-- Comptes clients
INSERT INTO compte_client (numero, date_creation, client_id)
VALUES ('CC1001', '2024-01-10', 1),
       ('CC1002', '2024-02-15', 2);

-- Types de prêt
INSERT INTO type_pret (libelle, taux, delai_debut_remboursement)
VALUES ('Pret Habitat', 2.50, 0),
       ('Pret Auto', 3.20, 2),
       ('Pret Perso', 4.00, 1);

-- Modalités
INSERT INTO modalite (libelle, nb_mois)
VALUES ('Annuelle', 12);

-- Enum status prêt
INSERT INTO enum_status_pret (libelle)
VALUES ('En attente'),
       ('Accepté'),
       ('Refusé');
