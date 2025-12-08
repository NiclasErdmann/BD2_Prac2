-- ============================================================
-- INSERTS DE PRUEBA - Orden correcto respetando las FKs
-- ============================================================

-- 1. AYUNTAMIENTO (no tiene dependencias)
INSERT INTO AYUNTAMIENTO (nombre) VALUES 
('Palma de Mallorca'),
('Inca'),
('Manacor');

-- 2. PERSONA (no tiene dependencias)
INSERT INTO PERSONA (nombre, apellido, usuario, contrasena, email, telefono) VALUES 
('Juan', 'García', 'juan', '123', 'juan@email.com', '971123456'),
('María', 'López', 'maria', '123', 'maria@email.com', '971234567'),
('Pedro', 'Martínez', 'pedro', '123', 'pedro@email.com', '971345678'),
('Ana', 'Sánchez', 'ana', '123', 'ana@email.com', '971456789');

-- 3. GRUPO_TRABAJO (depende de AYUNTAMIENTO, pero idResponsable lo dejamos NULL por ahora)
INSERT INTO GRUPO_TRABAJO (nombre, descripcion, idResponsable, idAyuntamiento) VALUES 
('Grupo Centro', 'Grupo de trabajo del centro de Palma', NULL, 1),
('Grupo Norte', 'Grupo de trabajo zona norte', NULL, 1),
('Grupo Inca', 'Grupo de trabajo de Inca', NULL, 2);

-- 4. VOLUNTARIO (depende de AYUNTAMIENTO, GRUPO_TRABAJO, PERSONA)
INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES 
(1, 1, 1),  -- Juan en Grupo Centro
(1, 2, 2),  -- María en Grupo Norte
(2, 3, 3);  -- Pedro en Grupo Inca

-- 5. Ahora actualizamos los responsables de los grupos
UPDATE GRUPO_TRABAJO SET idResponsable = 1 WHERE idGrupoTrabajo = 1;
UPDATE GRUPO_TRABAJO SET idResponsable = 2 WHERE idGrupoTrabajo = 2;
UPDATE GRUPO_TRABAJO SET idResponsable = 3 WHERE idGrupoTrabajo = 3;

-- 6. ADMINAYU (depende de AYUNTAMIENTO, PERSONA)
INSERT INTO ADMINAYU (idAyuntamiento, idPersona) VALUES 
(1, 4);  -- Ana es admin de Palma

-- 7. CEMENTERIO (no tiene dependencias)
INSERT INTO CEMENTERIO (nombre, ubicacion) VALUES 
('Cementerio Felino Palma', 'Calle de los Gatos, 123, Palma'),
('Cementerio Felino Inca', 'Avenida de los Mininos, 45, Inca');

-- 8. COLONIA_FELINA (depende de GRUPO_TRABAJO)
INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo) VALUES 
('Colonia Plaza Mayor', 'Colonia en la Plaza Mayor de Palma', '39.5696, 2.6502', 'Detrás del Ayuntamiento', 12, 1),
('Colonia Parque del Mar', 'Colonia junto al mar', '39.5665, 2.6499', 'Zona del parque', 8, 1),
('Colonia Son Espanyolet', 'Colonia en barrio residencial', '39.5743, 2.6389', 'Cerca de la gasolinera', 15, 2),
('Colonia Inca Centro', 'Colonia en el centro de Inca', '39.7214, 2.9108', 'Plaza del mercado', 10, 3);

-- 9. GATO (depende de CEMENTERIO - puede ser NULL)
-- Nota: Algunos gatos no tienen XIP asignado aún (NULL)
-- Hay dos "Bigotes" en colonias diferentes para probar el filtrado por colonia
INSERT INTO GATO (numXIP, nombre, sexo, descripcion, foto, idCementerio) VALUES 
('XIP001', 'Misu', 'Hembra', 'Gata blanca con manchas negras, muy cariñosa', NULL, NULL),
('XIP002', 'Felix', 'Macho', 'Gato naranja, un poco tímido', NULL, NULL),
('XIP003', 'Luna', 'Hembra', 'Gata negra con ojos verdes', NULL, NULL),
('XIP004', 'Tom', 'Macho', 'Gato gris atigrado, sociable', NULL, NULL),
('XIP005', 'Nieve', 'Hembra', 'Gata blanca de ojos azules', NULL, NULL),
('XIP006', 'Bigotes', 'Macho', 'Gato blanco y negro, muy activo', NULL, NULL),
('XIP007', 'Pelusa', 'Hembra', 'Gata gris de pelo largo', NULL, NULL),
('XIP008', 'Garfield', 'Macho', 'Gato naranja gordo', NULL, 1),  -- Este está fallecido
(NULL, 'Bigotes', 'Macho', 'Gato atigrado sin XIP aún', NULL, NULL),  -- Otro Bigotes SIN XIP en colonia diferente
(NULL, 'Manchitas', 'Hembra', 'Gata tricolor recién rescatada, sin XIP', NULL, NULL),
('XIP009', 'Solete', 'Macho', 'Gato beige muy juguetón', NULL, NULL),
(NULL, 'Sombra', 'Hembra', 'Gata negra muy esquiva, sin XIP aún', NULL, NULL);

-- 10. HISTORIAL (depende de GATO, COLONIA_FELINA)
-- Los gatos activos tienen fechaIda = NULL
-- Hay dos "Bigotes" en colonias diferentes: uno con XIP006 en colonia 3, otro sin XIP en colonia 1
INSERT INTO HISTORIAL (fechaLlegada, fechaIda, idGato, idColonia) VALUES 
('2024-01-15', NULL, 1, 1),      -- Misu en Plaza Mayor
('2024-02-20', NULL, 2, 1),      -- Felix en Plaza Mayor
('2024-03-10', NULL, 3, 2),      -- Luna en Parque del Mar
('2024-01-25', NULL, 4, 2),      -- Tom en Parque del Mar
('2024-04-05', NULL, 5, 3),      -- Nieve en Son Espanyolet
('2024-02-15', NULL, 6, 3),      -- Bigotes (XIP006) en Son Espanyolet
('2024-03-20', NULL, 7, 4),      -- Pelusa en Inca Centro
('2023-12-01', '2024-11-15', 8, 1),  -- Garfield estaba en Plaza Mayor, falleció
('2024-11-01', NULL, 9, 1),      -- Bigotes (sin XIP) en Plaza Mayor - ¡MISMO NOMBRE, COLONIA DIFERENTE!
('2024-11-10', NULL, 10, 2),     -- Manchitas en Parque del Mar
('2024-11-15', NULL, 11, 4),     -- Solete en Inca Centro
('2024-11-20', NULL, 12, 3);     -- Sombra en Son Espanyolet

-- 11. INCIDENCIA (depende de VOLUNTARIO, GATO)
-- TODAS las incidencias deben tener un gato asociado (idGato NOT NULL en la práctica)
INSERT INTO INCIDENCIA (fecha, descripcion, tipo, idVoluntario, idGato) VALUES 
('2024-11-20', 'Misu tiene tos y estornudos frecuentes', 'salud', 1, 1),
('2024-11-25', 'Felix tiene una herida en la pata delantera derecha', 'herido', 1, 2),
('2024-11-15', 'Garfield encontrado sin vida', 'fallecimiento', 1, 8),
('2024-12-01', 'Luna tiene conjuntivitis', 'salud', 1, 3),
('2024-12-03', 'Bigotes de Plaza Mayor (sin XIP) parece desnutrido', 'salud', 1, 9),  -- El Bigotes SIN XIP
('2024-12-04', 'Bigotes de Son Espanyolet (XIP006) tiene herida en oreja', 'herido', 2, 6),  -- El otro Bigotes CON XIP
('2024-12-05', 'Manchitas tiene pulgas', 'salud', 1, 10),
('2024-12-06', 'Sombra muy asustada, no se deja acercar', 'otro', 2, 12);

-- 12. ROL (no tiene dependencias)
INSERT INTO ROL (nombre) VALUES 
('Administrador'),
('Voluntario'),
('Coordinador'),
('Veterinario');

-- 13. FUNCION (no tiene dependencias)
INSERT INTO FUNCION (nombre, ruta) VALUES 
('Gestionar Colonias', '/colonias'),
('Registrar Incidencias', '/incidencias'),
('Ver Reportes', '/reportes'),
('Gestionar Usuarios', '/usuarios');

-- 14. PER_ROL (depende de PERSONA, ROL)
INSERT INTO PER_ROL (idPersona, idRol) VALUES 
(1, 2),  -- Juan es Voluntario
(2, 3),  -- María es Coordinadora
(3, 2),  -- Pedro es Voluntario
(4, 1);  -- Ana es Administrador

-- 15. PUEDEHACER (depende de ROL, FUNCION)
INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4),  -- Admin puede todo
(2, 1), (2, 2),                   -- Voluntario: colonias e incidencias
(3, 1), (3, 2), (3, 3);          -- Coordinador: colonias, incidencias y reportes

-- 16. MARCACOMIDA (no tiene dependencias)
INSERT INTO MARCACOMIDA (nombre, calidad, caracteristicas) VALUES 
('Royal Canin', 'Premium', 'Comida de alta calidad para gatos'),
('Whiskas', 'Estándar', 'Comida económica y nutritiva'),
('Purina', 'Buena', 'Buen balance calidad-precio');

-- 17. TRABAJO (depende de MARCACOMIDA, COLONIA_FELINA, VOLUNTARIO)
INSERT INTO TRABAJO (descripcion, fecha, hora, estado, idMarcaComida, idColonia, idVoluntario) VALUES 
('Alimentación diaria', '2024-12-06', '08:00:00', 'Completado', 1, 1, 1),
('Alimentación diaria', '2024-12-06', '08:30:00', 'Completado', 2, 2, 1),
('Limpieza de comederos', '2024-12-06', '09:00:00', 'Completado', NULL, 1, 1),
('Alimentación diaria', '2024-12-06', '18:00:00', 'Pendiente', 2, 3, 2);

-- 18. CENTRO_VETERINARIO (no tiene dependencias)
INSERT INTO CENTRO_VETERINARIO (nombre, mail, telefono, direccion) VALUES 
('Clínica Veterinaria Palma', 'info@vetpalma.com', '971111111', 'Calle Veterinaria, 1, Palma'),
('Centro Veterinario Son Espanyolet', 'contacto@vetson.com', '971222222', 'Avenida Son Espanyolet, 50, Palma');

-- 19. TIPO (no tiene dependencias)
INSERT INTO TIPO (tipoCampaña, tipoVacuna) VALUES 
('Esterilización', NULL),
('Vacunación', 'Rabia'),
('Vacunación', 'Triple Felina'),
('Desparasitación', NULL);

-- 20. CAMPAÑA_INTERVENCION (depende de CENTRO_VETERINARIO, COLONIA_FELINA, TIPO)
INSERT INTO CAMPAÑA_INTERVENCION (fechaInicio, fechaFin, descripcion, idCentroVet, idColonia, idTipo) VALUES 
('2024-10-01', '2024-10-15', 'Campaña de esterilización otoño', 1, 1, 1),
('2024-11-01', '2024-11-05', 'Vacunación contra rabia', 1, 2, 2),
('2024-11-10', '2024-11-20', 'Esterilización masiva', 2, 3, 1);

-- 21. PROFESIONAL (depende de PERSONA, CENTRO_VETERINARIO)
-- Necesitamos crear nuevas personas que sean profesionales
INSERT INTO PERSONA (nombre, apellido, usuario, contrasena, email, telefono) VALUES 
('Dr. Carlos', 'Ruiz', 'cruiz', '123', 'carlos@vetpalma.com', '971333333'),
('Dra. Laura', 'Fernández', 'lfernandez', '123', 'laura@vetson.com', '971444444');

INSERT INTO PROFESIONAL (activo, idPersona, idCentroVet) VALUES 
(TRUE, 5, 1),  -- Dr. Carlos en Clínica Palma
(TRUE, 6, 2);  -- Dra. Laura en Centro Son Espanyolet

-- 22. PARTICIPA (depende de CAMPAÑA_INTERVENCION, PROFESIONAL)
INSERT INTO PARTICIPA (idCampaña, idProfesional) VALUES 
(1, 1),  -- Dr. Carlos participa en campaña 1
(2, 1),  -- Dr. Carlos participa en campaña 2
(3, 2);  -- Dra. Laura participa en campaña 3

-- 23. ACCION_INDIVIDUAL (depende de GATO, PROFESIONAL, CAMPAÑA_INTERVENCION)
INSERT INTO ACCION_INDIVIDUAL (fecha, descripcion, autopsia, comentario, idGato, idProfesional, idCampaña) VALUES 
('2024-10-05', 'Esterilización', NULL, 'Operación sin complicaciones', 1, 1, 1),
('2024-10-08', 'Esterilización', NULL, 'Recuperación normal', 2, 1, 1),
('2024-11-02', 'Vacuna antirrábica', NULL, 'Sin reacciones adversas', 3, 1, 2),
('2024-11-15', 'Esterilización', NULL, 'Todo correcto', 5, 2, 3),
('2024-11-16', 'Autopsia', 'Muerte por edad avanzada y fallo multiorgánico', 'Gato de más de 15 años', 8, 1, NULL);

-- 24. COMENTARIO (depende de COLONIA_FELINA)
INSERT INTO COMENTARIO (contenidoComentario, idColonia) VALUES 
('Los gatos están muy bien cuidados, se nota el trabajo del equipo', 1),
('Necesitamos más comederos en esta colonia', 2),
('Excelente colaboración de los vecinos', 3);

-- 25. ALBIRAMENT (depende de GATO, COLONIA_FELINA)
INSERT INTO ALBIRAMENT (fechaVista, idGato, idColonia) VALUES 
('2024-12-05', 1, 1),  -- Misu vista en su colonia
('2024-12-05', 2, 1),  -- Felix visto en su colonia
('2024-12-04', 3, 2),  -- Luna vista en su colonia
('2024-12-06', 4, 2),  -- Tom visto en su colonia
('2024-12-06', 5, 3),  -- Nieve vista en su colonia
('2024-12-05', 9, 1),  -- Bigotes (sin XIP) visto en Plaza Mayor
('2024-12-05', 6, 3);  -- Bigotes (XIP006) visto en Son Espanyolet

-- ============================================================
-- FIN DE INSERTS
-- ============================================================
