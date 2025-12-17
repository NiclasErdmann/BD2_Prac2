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

INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES
    (1, NULL, 2),
    (1, NULL, 3),
    (1, NULL, 4),
    (1, NULL, 5),
    (1, NULL, 6),
    (1, NULL, 7),
    (1, NULL, 8),
    (1, NULL, 9),
    (1, NULL, 10),
    (1, NULL, 11),
    (1, NULL, 12),
    (1, NULL, 13);

INSERT INTO GRUPO_TRABAJO (nombre, descripcion, idResponsable, idAyuntamiento) VALUES
    ('Grupo Centro', 'Colonias del casco antiguo', 1, 1),
    ('Grupo Parques', 'Zonas verdes y parques', 2, 1);

UPDATE VOLUNTARIO SET idGrupoTrabajo = 1 WHERE idPersona IN (2, 4, 6, 7, 8, 9);
UPDATE VOLUNTARIO SET idGrupoTrabajo = 2 WHERE idPersona IN (3, 5, 10, 11, 12, 13);


INSERT INTO ADMINAYU (idAyuntamiento, idPersona) VALUES
    (1, 1);

INSERT INTO CEMENTERIO (nombre, ubicacion) VALUES
    ('Cementerio Municipal', 'Camino del Río s/n');

INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo) VALUES
    ('Colonia Centro', 'Gatos en el centro histórico', '40.0735,-88.2535', 'Plaza Mayor', 15, 1),
    ('Colonia Parque', 'Comunidad en el parque central', '40.0745,-88.2545', 'Parque Central', 22, 2);

INSERT INTO GATO (numXIP, nombre, sexo, descripcion, foto, idCementerio) VALUES
    ('XIP-001', 'Misu', 'H', 'Gata atigrada, muy cariñosa', 'imagenes/gatos/misu.jpg', NULL),
    ('XIP-002', 'Felix', 'M', 'Gato negro, tímido', 'imagenes/gatos/felix.jpg', NULL),
    ('XIP-003', 'Luna', 'H', 'Gata gris, sociable', 'imagenes/gatos/luna.jpg', NULL),
    ('XIP-004', 'Tom', 'M', 'Gato blanco con manchas', 'imagenes/gatos/tom.jpg', NULL),
    ('XIP-005', 'Nieve', 'H', 'Gata blanca de ojos azules', 'imagenes/gatos/nieve.jpg', NULL),
    (NULL, 'Bigotes', 'M', 'Gato atigrado sin XIP aún', NULL, NULL),
    (NULL, 'Sombra', 'H', 'Gata negra muy esquiva', NULL, NULL),
    ('XIP-006', 'Garfield', 'M', 'Gato naranja, falleció', 'imagenes/gatos/garfield.jpg', 1);

INSERT INTO HISTORIAL (fechaLlegada, fechaIda, idGato, idColonia) VALUES
    ('2024-01-10', NULL, 1, 1),   -- Misu en Colonia Centro
    ('2024-02-05', NULL, 2, 1),   -- Felix en Colonia Centro
    ('2024-03-15', NULL, 3, 1),   -- Luna en Colonia Centro
    ('2024-01-20', NULL, 4, 2),   -- Tom en Colonia Parque
    ('2024-02-15', NULL, 5, 2),   -- Nieve en Colonia Parque
    ('2024-03-01', NULL, 6, 2),   -- Bigotes en Colonia Parque
    ('2024-03-10', NULL, 7, 2),   -- Sombra en Colonia Parque
    ('2023-12-01', '2024-05-01', 8, 1);  -- Garfield estaba en Centro, falleció

INSERT INTO INCIDENCIA (fecha, descripcion, tipo, idVoluntario, idGato) VALUES
    ('2024-04-01', 'Misu tiene tos y estornudos', 'salud', 1, 1),
    ('2024-04-10', 'Felix con herida en pata', 'herido', 1, 2),
    ('2024-05-01', 'Garfield encontrado sin vida', 'fallecimiento', 1, 8),
    ('2024-04-15', 'Tom con conjuntivitis', 'salud', 2, 4),
    ('2024-04-20', 'Nieve desnutrida', 'salud', 2, 5),
    ('2024-04-25', 'Sombra muy asustada', 'otro', 2, 7);

INSERT INTO ROL (nombre) VALUES
    ('adminAyuntamiento'),
    ('responsableGrupo'),
    ('voluntario');

INSERT INTO FUNCION (nombre, ruta) VALUES
    ('Modificar Colonias', 'BD249482420/crearColonia.html'),
    ('Ver Colonias', 'BD249482420/listar_colonias.html'),
    ('Gestionar Grupos', 'BD249482420/listar_grupoTrabajo.php'),
    ('Ver Grupos', 'BD249482420/listar_grupoTrabajo.php'),
    ('Borsi Voluntarios', 'BD249482420/gestionarBorsi.php'),
    ('Mis Incidencias', 'BD249772780/listar_incidencias.php'),
    ('Planificar Trabajo', 'BD249772780/planificar_trabajo.php'),
    ('Albirament Gato', 'BD249772780/listar_gatos.php?modo=albirament'),
    ('Ver Gatos', 'BD249772780/listar_gatos.php?modo=ver'),
    ('Modificacion Permisos', 'BD24550587/modifica_permisos.php'),
    ('Consulta Mis Tareas', 'BD24550587/consulta_lista_tareas.php');

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
    -- adminAyuntamiento puede todo
    (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1,10), (1,11),
    -- responsableGrupo puede ver y gestionar algunas cosas
    (2, 2), (2, 4), (2, 6), (2, 7), (2, 8), (2, 9), (2,11),
    -- voluntario puede ver grupos, registrar incidencias, albiraments y ver gatos
    (3, 4), (3, 6), (3, 8), (3, 9), (3,11);

INSERT INTO MARCACOMIDA (nombre, calidad, caracteristicas, pesoPorGato) VALUES
    ('CatPlus', 'Alta', 'Rica en proteínas', 100),
    ('Whiskas', 'Media', 'Comida económica y nutritiva', 150),
    ('Royal Canin', 'Premium', 'Alta gama para gatos', 125);

INSERT INTO TRABAJO (descripcion, fecha, hora, estado, comentario, idMarcaComida, idColonia, idVoluntario) VALUES
    ('Alimentación diaria', '2024-04-15', '08:00:00', 'completado', "los informaticos son muy creativos", 1, 1, 1),
    ('Alimentación diaria', '2024-04-16', '08:00:00', 'completado', NULL, 1, 1, 1),
    ('Alimentación diaria', '2024-04-17', '08:00:00', 'pendiente', NULL, 1, 1, 1),
    ('Alimentación diaria', '2024-04-18', '08:00:00', 'pendiente', NULL, 1, 1, 1),
    ('Alimentación diaria', '2024-04-18', '16:00:00', 'pendiente', NULL, 1, 1, 1),
    ('Revisión zona parque', '2024-04-16', '10:30:00', 'completado', NULL, NULL, 2, 2),
    ('Limpieza comederos', '2024-04-17', '09:00:00', 'completado', NULL, NULL, 1, 3),
    ('Alimentación nocturna', '2024-04-17', '20:00:00', 'completado', "era de noche", 2, 2, 4),
    ('Revisión general', '2024-04-18', '11:00:00', 'completado', NULL, NULL, 1, 5),
    ('Alimentación diaria', '2024-04-19', '08:00:00', 'pendiente', NULL, 3, 2, 6);

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
    ('2024-04-20', 1, 1),   -- Misu vista en Centro
    ('2024-04-21', 2, 1),   -- Felix visto en Centro
    ('2024-04-22', 3, 1),   -- Luna vista en Centro
    ('2024-04-20', 4, 2),   -- Tom visto en Parque
    ('2024-04-21', 5, 2),   -- Nieve vista en Parque
    ('2024-04-22', 6, 2),   -- Bigotes visto en Parque
    ('2024-04-23', 7, 2);   -- Sombra vista en Parque

INSERT INTO FUNCION (nombre, ruta) VALUES ('Registrar Gato', 'BD243468864/crear_gato.php');

-- Autorizar al Administrador (Rol 1)
INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES (1, 12);

-- Autorizar al Responsable de Grupo (Rol 2)
INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES (2, 12);

-- Autorizar al Voluntario común (Rol 3)
INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES (3, 12);