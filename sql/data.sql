insert into type_pret(libelle,taux) values
("type 1", 1.5),
("type 2", 2.0),
("type 3", 2.5);

insert into modalite(libelle, nb_mois) values
("Modalité 1", 1),
("Modalité 2", 3),
("Modalité 3", 6),
("Modalité 4", 12);

drop database banque;
create database banque;
use banque;