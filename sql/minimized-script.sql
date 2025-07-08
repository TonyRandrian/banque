CREATE TABLE exams4_enum_status_pret
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_type_pret
(
    id                        INT AUTO_INCREMENT,
    libelle                   VARCHAR(50)    NOT NULL,
    taux                      DECIMAL(15, 2) NOT NULL,
    delai_debut_remboursement INT DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_modalite
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(100),
    nb_mois INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_client
(
    id     INT AUTO_INCREMENT,
    email  VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    mdp    VARCHAR(255) NOT NULL,
    nom    VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_config
(
    id               INT AUTO_INCREMENT,
    libelle          VARCHAR(255) NOT NULL,
    date_application DATE         NOT NULL,
    valeur           VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_tresorerie
(
    id             INT AUTO_INCREMENT,
    date_mouvement DATE           NOT NULL,
    solde          DECIMAL(15, 2) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE exams4_compte_client
(
    id            INT AUTO_INCREMENT,
    numero        VARCHAR(50) NOT NULL,
    date_creation DATE,
    client_id     INT         NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (client_id) REFERENCES exams4_client (id)
);

CREATE TABLE exams4_pret
(
    id                  INT AUTO_INCREMENT,
    duree_remboursement DECIMAL(15, 2) NOT NULL,
    montant             DECIMAL(15, 2) NOT NULL,
    date_demande        DATE           NOT NULL,
    modalite_id         INT            NOT NULL,
    compte_client_id    INT            NOT NULL,
    type_pret_id        INT            NOT NULL,
    taux_assurance      DECIMAL(15, 2)          DEFAULT 0.00,
    assurance_par_mois  BOOLEAN        NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (modalite_id) REFERENCES exams4_modalite (id),
    FOREIGN KEY (compte_client_id) REFERENCES exams4_compte_client (id),
    FOREIGN KEY (type_pret_id) REFERENCES exams4_type_pret (id)
);

CREATE TABLE exams4_simulation_pret
(
    id                  INT AUTO_INCREMENT,
    duree_remboursement DECIMAL(15, 2) NOT NULL,
    montant             DECIMAL(15, 2) NOT NULL,
    date_demande        DATE           NOT NULL,
    modalite_id         INT            NOT NULL,
    type_pret_id        INT            NOT NULL,
    taux_assurance      DECIMAL(15, 2)          DEFAULT 0.00,
    assurance_par_mois  BOOLEAN        NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (modalite_id) REFERENCES exams4_modalite (id),
    FOREIGN KEY (type_pret_id) REFERENCES exams4_type_pret (id)
);

CREATE TABLE exams4_status_pret
(
    id           INT AUTO_INCREMENT,
    date_status  DATE NOT NULL,
    enum_pret_id INT  NOT NULL,
    pret_id      INT  NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (enum_pret_id) REFERENCES exams4_enum_status_pret (id),
    FOREIGN KEY (pret_id) REFERENCES exams4_pret (id)
);

CREATE TABLE exams4_paiement_modalite
(
    id                 INT AUTO_INCREMENT,
    date_prevu_paiment DATE           NOT NULL,
    montant_prevu      DECIMAL(15, 2) NOT NULL,
    mensualite         DECIMAL(15, 2) NOT NULL,
    interet            DECIMAL(15, 2) NOT NULL,
    amortissement      DECIMAL(15, 2) NOT NULL,
    assurance          DECIMAL(15, 2) NOT NULL,
    montant_restant    DECIMAL(15, 2) NOT NULL,
    pret_id            INT            NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (pret_id) REFERENCES exams4_pret (id)
);

CREATE TABLE exams4_simulation_paiement_modalite
(
    id                 INT AUTO_INCREMENT,
    date_prevu_paiment DATE           NOT NULL,
    montant_prevu      DECIMAL(15, 2) NOT NULL,
    mensualite         DECIMAL(15, 2) NOT NULL,
    interet            DECIMAL(15, 2) NOT NULL,
    amortissement      DECIMAL(15, 2) NOT NULL,
    assurance          DECIMAL(15, 2) NOT NULL,
    montant_restant    DECIMAL(15, 2) NOT NULL,
    simulation_pret_id INT            NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (simulation_pret_id) REFERENCES exams4_simulation_pret (id)
);