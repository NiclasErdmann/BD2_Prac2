-- Datos base para el nuevo esquema

INSERT INTO AYUNTAMIENTO (nombre) VALUES
    ('Cuenca');

INSERT INTO PERSONA (nombre, apellido, usuario, contrasena, email, telefono) VALUES
    ('Ana', 'Martinez', 'ana.admin', 'passAdmin', 'ana@cuenca.es', '600111222'),
    ('Juan', 'Lopez', 'juan.vol', 'passJuan', 'juan@cuenca.es', '600222333'),
    ('Maria', 'Perez', 'maria.vet', 'passMaria', 'maria@cuenca.es', '600333444'),
    ('Carlos', 'Rodríguez', 'carlos.resp', 'passCarlos', 'carlos@cuenca.es', '600444555'),
    ('Laura', 'González', 'laura.resp', 'passLaura', 'laura@cuenca.es', '600555666'),
    ('Miguel', 'Sánchez', 'miguel.vol1', 'passMiguel', 'miguel1@cuenca.es', '600666777'),
    ('Isabel', 'García', 'isabel.vol1', 'passIsabel', 'isabel1@cuenca.es', '600777888'),
    ('Pedro', 'Fernández', 'pedro.vol1', 'passPedro', 'pedro1@cuenca.es', '600888999'),
    ('Sofia', 'Ruiz', 'sofia.vol1', 'passSofia', 'sofia1@cuenca.es', '600999111'),
    ('David', 'Morales', 'david.vol2', 'passDavid', 'david2@cuenca.es', '601111222'),
    ('Elena', 'Castro', 'elena.vol2', 'passElena', 'elena2@cuenca.es', '601222333'),
    ('Roberto', 'Jiménez', 'roberto.vol2', 'passRoberto', 'roberto2@cuenca.es', '601333444'),
    ('Patricia', 'Vargas', 'patricia.vol2', 'passPatricia', 'patricia2@cuenca.es', '601444555');

INSERT INTO GRUPO_TRABAJO (nombre, descripcion, idResponsable, idAyuntamiento) VALUES
    ('Grupo Centro', 'Colonias del casco antiguo', 3, 1),
    ('Grupo Parques', 'Zonas verdes y parques', 4, 1);

INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES
    (1, 1, 2),
    (1, 2, 3),
    (1, 1, 4),
    (1, 2, 5),
    (1, 1, 6),
    (1, 1, 7),
    (1, 1, 8),
    (1, 1, 9),
    (1, 2, 10),
    (1, 2, 11),
    (1, 2, 12),
    (1, 2, 13);


INSERT INTO ADMINAYU (idAyuntamiento, idPersona) VALUES
    (1, 1);

INSERT INTO CEMENTERIO (nombre, ubicacion) VALUES
    ('Cementerio Municipal', 'Camino del Río s/n');

INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo) VALUES
    ('Colonia Centro', 'Gatos en el centro histórico', '40.0735,-88.2535', 'Plaza Mayor', 15, 1),
    ('Colonia Parque', 'Comunidad en el parque central', '40.0745,-88.2545', 'Parque Central', 22, 2);

INSERT INTO GATO (numXIP, descripcion, foto, idCementerio) VALUES
    ('XIP-001', 'Gato atigrado', NULL, NULL),
    ('XIP-002', 'Gata negra', NULL, NULL),
    ('XIP-003', 'Gato gris', NULL, 1);

INSERT INTO HISTORIAL (fechaLlegada, fechaIda, idGato, idColonia) VALUES
    ('2024-01-10', NULL, 1, 1),
    ('2024-02-05', NULL, 2, 2),
    ('2024-03-15', '2024-05-01', 3, 1);

INSERT INTO INCIDENCIA (fecha, descripcion, tipo, idVoluntario, idGato) VALUES
    ('2024-04-01', 'Revisión veterinaria', 'sanitaria', 1, 1),
    ('2024-04-10', 'Nueva camada vista', 'observacion', 2, NULL);

INSERT INTO ROL (nombre) VALUES
    ('adminAyuntamiento'),
    ('responsableGrupo'),
    ('voluntario');

INSERT INTO FUNCION (nombre, ruta) VALUES
    ('Modificar Colonias', 'estela/crearColonia.html'),
    ('Ver Colonias', 'estela/listar_colonias.html'),
    ('Gestionar Grupos', 'estela/listar_grupoTrabajo.php'),
    ('Ver Grupos', 'estela/listar_grupoTrabajo.php'),
    ('Registrar Incidencia', 'AÑADIR RUTA AQUÍ'),
    ('Planificar Trabajo', 'AÑADIR RUTA AQUÍ');

INSERT INTO PER_ROL (idPersona, idRol) VALUES
    (1, 1),
    (2, 2),
    (3, 2),
    (4, 2),
    (5, 2),
    (6, 3),
    (7, 3),
    (8, 3),
    (9, 3),
    (10, 3),
    (11, 3),
    (12, 3),
    (13, 3);

INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES
    (1, 1),
    (1, 2),
    (1, 3),
    (1, 4),
    (1, 5),
    (1, 6),
    (2, 2),
    (2, 4),
    (3, 4),
    (3, 4);

INSERT INTO MARCACOMIDA (nombre, calidad, caracteristicas) VALUES
    ('CatPlus', 'Alta', 'Rica en proteínas');

INSERT INTO TRABAJO (descripcion, fecha, hora, estado, idMarcaComida, idColonia, idVoluntario) VALUES
    ('Alimentación diaria', '2024-04-15', '08:00:00', 'pendiente', 1, 1, 1),
    ('Revisión zona parque', '2024-04-16', '10:30:00', 'completado', NULL, 2, 2);

INSERT INTO CENTRO_VETERINARIO (nombre, mail, telefono, direccion) VALUES
    ('Clínica Vet Cuenca', 'contacto@vetcuenca.es', '969111222', 'Av. Castilla 10');

INSERT INTO TIPO (tipoCampaña, tipoVacuna) VALUES
    ('Esterilización', 'Rabia'),
    ('Desparasitación', 'Moquillo');

INSERT INTO CAMPAÑA_INTERVENCION (fechaInicio, fechaFin, descripcion, idCentroVet, idColonia, idTipo) VALUES
    ('2024-05-01', '2024-05-15', 'Campaña esterilización primavera', 1, 1, 1),
    ('2024-06-01', NULL, 'Desparasitación trimestral', 1, 2, 2);

INSERT INTO PROFESIONAL (activo, idPersona, idCentroVet) VALUES
    (TRUE, 3, 1);

INSERT INTO PARTICIPA (idCampaña, idProfesional) VALUES
    (1, 1),
    (2, 1);

INSERT INTO ACCION_INDIVIDUAL (fecha, descripcion, autopsia, comentario, idGato, idProfesional, idCampaña) VALUES
    ('2024-05-02', 'Esterilización XIP-001', NULL, 'Recuperación estable', 1, 1, 1),
    ('2024-06-03', 'Desparasitación XIP-002', NULL, 'Sin incidencias', 2, 1, 2);

INSERT INTO COMENTARIO (contenidoComentario, idColonia) VALUES
    ('Colonia muy colaborativa con vecinos.', 1),
    ('Requiere más refugios en invierno.', 2);

INSERT INTO ALBIRAMENT (fechaVista, idGato, idColonia) VALUES
    ('2024-04-20', 1, 1),
    ('2024-04-22', 2, 2);

