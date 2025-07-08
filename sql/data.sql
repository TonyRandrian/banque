drop database banque;
create database banque;
use banque;

-- Jeu de données pour tester la simulation de prêt

-- Clients
INSERT INTO client (email, prenom, mdp, nom) VALUES
('alice@example.com', 'Alice', 'pass1', 'Durand'),
('bob@example.com', 'Bob', 'pass2', 'Martin');

-- Comptes clients
INSERT INTO compte_client (numero, date_creation, client_id) VALUES
('CC1001', '2024-01-10', 1),
('CC1002', '2024-02-15', 2);

-- Types de prêt
INSERT INTO type_pret (libelle, taux, delai_debut_remboursement) VALUES
('Pret Habitat', 2.50, 0),
('Pret Auto', 3.20, 2),
('Pret Perso', 4.00, 1);

-- Modalités
INSERT INTO modalite (libelle, nb_mois) VALUES
('Annuelle', 12);

-- Enum status prêt
INSERT INTO enum_status_pret (libelle) VALUES
('En attente'),
('Accepté'),
('Refusé');