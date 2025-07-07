CREATE TABLE enum_status_pret
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE type_pret
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(50)    NOT NULL,
    taux    DECIMAL(15, 2) NOT NULL,
    taux_assurance DECIMAL(15, 2) DEFAULT 0.00,
    delai_debut_remboursement INT DEFAULT 0 ,
    PRIMARY KEY (id)
);

CREATE TABLE modalite
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(100),
    nb_mois INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE client
(
    id     INT AUTO_INCREMENT,
    email  VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    mdp    VARCHAR(255) NOT NULL,
    nom    VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE employe
(
    id     INT AUTO_INCREMENT,
    nom    VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email  VARCHAR(255) NOT NULL,
    mdp    VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE compte_client
(
    id            INT AUTO_INCREMENT,
    numero        VARCHAR(50) NOT NULL,
    date_creation DATE,
    client_id     INT         NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (client_id) REFERENCES client (id)
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

CREATE TABLE compte_client
(
    id            INT AUTO_INCREMENT,
    numero        VARCHAR(50) NOT NULL,
    date_creation DATE,
    client_id     INT         NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (client_id) REFERENCES client (id)
);

CREATE TABLE mouvement_solde
(
    id               INT AUTO_INCREMENT,
    montant          DECIMAL(15, 2) NOT NULL,
    date_mouvement   DATE,
    compte_client_id INT            NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (compte_client_id) REFERENCES compte_client (id)
);

CREATE TABLE enum_status_compte
(
    id      INT AUTO_INCREMENT,
    libelle VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE status_pret
(
    id           INT AUTO_INCREMENT,
    date_status  DATE NOT NULL,
    employees_id INT  NOT NULL,
    enum_pret_id INT  NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (employees_id) REFERENCES employe (id),
    FOREIGN KEY (enum_pret_id) REFERENCES enum_status_pret (id)
);

CREATE TABLE pret
(
    id                  INT AUTO_INCREMENT,
    duree_remboursement INT            NOT NULL,
    montant             DECIMAL(15, 2) NOT NULL,
    date_demande        INT            NOT NULL,
    modalite_id         INT            NOT NULL,
    employees_id        INT            NOT NULL,
    compte_client_id    INT            NOT NULL,
    type_pret_id        INT            NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (modalite_id) REFERENCES modalite (id),
    FOREIGN KEY (employees_id) REFERENCES employe (id),
    FOREIGN KEY (compte_client_id) REFERENCES compte_client (id),
    FOREIGN KEY (type_pret_id) REFERENCES type_pret (id)
);

CREATE TABLE paiement
(
    id            INT AUTO_INCREMENT,
    date_paiement DATE           NOT NULL,
    montant       DECIMAL(15, 2) NOT NULL,
    pret_id       INT            NOT NULL,
    employees_id  INT            NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (pret_id) REFERENCES pret (id),
    FOREIGN KEY (employees_id) REFERENCES employe (id)
);

CREATE TABLE paiement_modalite
(
    id                 INT AUTO_INCREMENT,
    date_prevu_paiment DATE           NOT NULL,
    montant_prevu      DECIMAL(15, 2) NOT NULL,
    pret_id            INT            NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (pret_id) REFERENCES pret (id)
);

CREATE TABLE status_client
(
    compte_client_id      INT,
    enum_status_compte_id INT,
    date_status           DATE NOT NULL,
    PRIMARY KEY (compte_client_id, enum_status_compte_id),
    FOREIGN KEY (compte_client_id) REFERENCES compte_client (id),
    FOREIGN KEY (enum_status_compte_id) REFERENCES enum_status_compte (id)
);