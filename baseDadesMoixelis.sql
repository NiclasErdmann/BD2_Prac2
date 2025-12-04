create database 20251113privilegis;

create table rol (
    rol varchar(16) primary key
);
create table usuari (
    username varchar(16) primary key,
    esunrol varchar(16) not null,
    constraint usu_rol foreign key (esunrol) references rol(rol)
);
create table privilegis (
    idprivilegi int auto_increment primary key,
    titol varchar(24),
    enlace varchar(24)
);
create table potfer (
    rol varchar(16),
    privilegi int,
    constraint rolpotfer foreign key (rol) references rol(rol),
    constraint potferpri foreign key (privilegi)
        references privilegis(idprivilegi) 
);
insert into rol (rol) values ("admin"),("convidat");
insert into usuari (username,esunrol)
    values
    ("Joan","admin"),
    ("Aina","convidat");
insert into privilegis (titol,enlace)
    values
    ("Inserir moix","inserir.php"),
    ("Veure moixos","consultamoixos.php"),
    ("Fer autòpsia","analitzar.php"),
    ("Alimentar","alimenta.php");
insert into potfer (rol,privilegi)
    values
    ("admin",1),
    ("admin",2),
    ("admin",3),
    ("admin",4),
    ("convidat",2);
