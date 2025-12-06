insert into Rol (nombre) values 
    ("adminGovern"),
    ("voluntario"),
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

insert into GrupoTrabajo (nombre, descripcion, idAyuntamiento) values
    ('Grupo Cuidado Centro', 'Equipo responsable de las colonias del centro', 1),
    ('Grupo Mantenimiento Parques', 'Voluntarios enfocados en parques y espacios verdes', 1),
    ('Grupo Monitoreo', 'Equipo de seguimiento y documentación de colonias', 1);

insert into ColoniaFelina (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo, idAyuntamiento) values
    ('Colonia Centro Cuenca', 'Colonia de gatos ubicada en el centro histórico', '40.0735,-88.2535', 'Plaza Mayor', 15, 1, 1),
    ('Colonia Parque Verde', 'Comunidad de gatos salvajes del parque', '40.0745,-88.2545', 'Parque Central', 22, 2, 1),
    ('Colonia Barrio Antiguo', 'Gatos residentes de las calles antiguas', '40.0755,-88.2555', 'Calle Mayor', 18, 1, 1),
    ('Colonia Zona Industrial', 'Colonia de gatos en área industrial', '40.0765,-88.2565', 'Polígono Industrial Sur', 25, 3, 1),
    ('Colonia Riberas', 'Comunidad felina cerca del río Júcar', '40.0775,-88.2575', 'Paseo Rivereño', 20, 2, 1);

