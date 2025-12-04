insert into Rol (nombre) values 
    ("admin"),
    ("convidat");

insert into Ayuntamiento(nombre)values
("cuenca");

insert into Persona (nombre,apellido,email,telefono)values
("nombre","jnsdjvds","mail","2342343"),
("nombre1","jnsdjvds1","mail1","2342343423");


insert into Voluntario (usuario,contraseña,idAyuntamiento,idPersona,idRol)values
("Juan","123",1,1,1),
("Aina","1234",1,2,2);

insert into Funciones (nombre)values
    ("inserir.php"),
    ("consultamoixos.php"),
    ("analitzar.php"),
    ("alimenta.php");

insert into PuedeHacer (idRol,idFunciones)values
    (1,1),
    (1,2),
    (1,3),
    (1,4),
    (2,2);