CREATE TABLE enum_status_pret
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE type_pret
(
    id            INT AUTO_INCREMENT,
    libelle       VARCHAR(50)    NOT NULL,
    taux          DECIMAL(15, 2) NOT NULL,
    taux_assurance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id)
);

CREATE TABLE modalite
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(100),
    nb_mois INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE config
(
    id               INT AUTO_INCREMENT,
    libelle          VARCHAR(255) NOT NULL,
    date_application DATE         NOT NULL,
    valeur           VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE tresorerie
(
    id             INT AUTO_INCREMENT,
    date_mouvement DATE           NOT NULL,
    solde          DECIMAL(15, 2) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE status_pret
(
    id           INT AUTO_INCREMENT,
    date_status  DATE NOT NULL,
    enum_pret_id INT  NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (enum_pret_id) REFERENCES enum_status_pret (id)
);

CREATE TABLE pret
(
    id                  INT AUTO_INCREMENT,
    duree_remboursement DECIMAL(15, 2) NOT NULL,
    montant             DECIMAL(15, 2) NOT NULL,
    date_demande        INT            NOT NULL,
    modalite_id         INT            NOT NULL,
    type_pret_id        INT            NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (modalite_id) REFERENCES modalite (id),
    FOREIGN KEY (type_pret_id) REFERENCES type_pret (id)
);

CREATE TABLE paiement_modalite
(
    id                 INT AUTO_INCREMENT,
    date_prevu_paiment DATE           NOT NULL,
    montant_prevu      DECIMAL(15, 2) NOT NULL,
    interet            DECIMAL(15, 2) NOT NULL,
    amortissement      DECIMAL(15, 2) NOT NULL,
    pret_id            INT            NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (pret_id) REFERENCES pret (id)
);