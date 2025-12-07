-- ============================================================
-- 1. AYUNTAMIENTO
-- ============================================================
CREATE TABLE AYUNTAMIENTO(
    idAyuntamiento INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL
);

-- ============================================================
-- 2. PERSONA
-- ============================================================
CREATE TABLE PERSONA(
    idPersona INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    apellido VARCHAR(200),
    usuario VARCHAR(200),
    contrasena VARCHAR(200),
    email VARCHAR(200),
    telefono VARCHAR(100)
);

-- ============================================================
-- 3. GRUPO_TRABAJO
-- ============================================================
CREATE TABLE GRUPO_TRABAJO(
    idGrupoTrabajo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    idResponsable INT,
    idAyuntamiento INT,
    CONSTRAINT FK_grupotrabajo_ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES AYUNTAMIENTO(idAyuntamiento)
);

-- ============================================================
-- 4. VOLUNTARIO
-- ============================================================
CREATE TABLE VOLUNTARIO(
    idVoluntario INT PRIMARY KEY AUTO_INCREMENT,
    idAyuntamiento INT NOT NULL,
    idGrupoTrabajo INT,
    idPersona INT NOT NULL,
    CONSTRAINT FK_voluntario_ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES AYUNTAMIENTO(idAyuntamiento),
    CONSTRAINT FK_voluntario_grupotrabajo
        FOREIGN KEY (idGrupoTrabajo) REFERENCES GRUPO_TRABAJO(idGrupoTrabajo),
    CONSTRAINT FK_voluntario_persona
        FOREIGN KEY (idPersona) REFERENCES PERSONA(idPersona)
);

-- ============================================================
-- 5. ADMINAYU
-- ============================================================
CREATE TABLE ADMINAYU(
    idAdmin INT PRIMARY KEY AUTO_INCREMENT,
    idAyuntamiento INT NOT NULL,
    idPersona INT NOT NULL,
    CONSTRAINT FK_adminayu_ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES AYUNTAMIENTO(idAyuntamiento),
    CONSTRAINT FK_adminayu_persona
        FOREIGN KEY (idPersona) REFERENCES PERSONA(idPersona)
);

-- ============================================================
-- 6. CEMENTERIO
-- ============================================================
CREATE TABLE CEMENTERIO(
    idCementerio INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200),
    ubicacion VARCHAR(300)
);

-- ============================================================
-- 7. COLONIA_FELINA
-- ============================================================
CREATE TABLE COLONIA_FELINA(
    idColonia INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    coordenadas VARCHAR(200) NOT NULL,
    lugarReferencia VARCHAR(200),
    numeroGatos INT DEFAULT 0,
    idGrupoTrabajo INT,
    CONSTRAINT FK_colonia_grupotrabajo
        FOREIGN KEY (idGrupoTrabajo) REFERENCES GRUPO_TRABAJO(idGrupoTrabajo)
);

-- ============================================================
-- 8. GATO
-- ============================================================
CREATE TABLE GATO(
    idGato INT PRIMARY KEY AUTO_INCREMENT,
    numXIP VARCHAR(200),
    descripcion TEXT,
    foto TEXT,
    idCementerio INT,
    CONSTRAINT FK_gato_cementerio
        FOREIGN KEY (idCementerio) REFERENCES CEMENTERIO(idCementerio)
);

-- ============================================================
-- 9. HISTORIAL
-- ============================================================
CREATE TABLE HISTORIAL(
    idHistorial INT PRIMARY KEY AUTO_INCREMENT,
    fechaLlegada DATE NOT NULL,
    fechaIda DATE,
    idGato INT NOT NULL,
    idColonia INT NOT NULL,
    CONSTRAINT FK_historial_gato
        FOREIGN KEY (idGato) REFERENCES GATO(idGato),
    CONSTRAINT FK_historial_colonia
        FOREIGN KEY (idColonia) REFERENCES COLONIA_FELINA(idColonia)
);

-- ============================================================
-- 10. INCIDENCIA
-- ============================================================
CREATE TABLE INCIDENCIA(
    idIncidencia INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(200),
    idVoluntario INT NOT NULL,
    idGato INT,
    CONSTRAINT FK_incidencia_voluntario
        FOREIGN KEY (idVoluntario) REFERENCES VOLUNTARIO(idVoluntario),
    CONSTRAINT FK_incidencia_gato
        FOREIGN KEY (idGato) REFERENCES GATO(idGato)
);

-- ============================================================
-- 11. ROL
-- ============================================================
CREATE TABLE ROL(
    idRol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200)
);

-- ============================================================
-- 12. FUNCION
-- ============================================================
CREATE TABLE FUNCION(
    idFuncion INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200)
);

-- ============================================================
-- 13. PER_ROL
-- ============================================================
CREATE TABLE PER_ROL(
    idPerRol INT PRIMARY KEY AUTO_INCREMENT,
    idPersona INT NOT NULL,
    idRol INT NOT NULL,
    CONSTRAINT FK_perrol_persona
        FOREIGN KEY (idPersona) REFERENCES PERSONA(idPersona),
    CONSTRAINT FK_perrol_rol
        FOREIGN KEY (idRol) REFERENCES ROL(idRol)
);

-- ============================================================
-- 14. PUEDEHACER
-- ============================================================
CREATE TABLE PUEDEHACER(
    idPuedeHacer INT PRIMARY KEY AUTO_INCREMENT,
    idRol INT,
    idFuncion INT NOT NULL,
    CONSTRAINT FK_puedehacer_rol
        FOREIGN KEY (idRol) REFERENCES ROL(idRol),
    CONSTRAINT FK_puedehacer_funcion
        FOREIGN KEY (idFuncion) REFERENCES FUNCION(idFuncion)
);

-- ============================================================
-- 15. MARCACOMIDA
-- ============================================================
CREATE TABLE MARCACOMIDA(
    idMarcaComida INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200),
    calidad VARCHAR(200),
    caracteristicas TEXT
);

-- ============================================================
-- 16. TRABAJO
-- ============================================================
CREATE TABLE TRABAJO(
    idTrabajo INT PRIMARY KEY AUTO_INCREMENT,
    descripcion TEXT,
    fecha DATE,
    hora TIME,
    estado VARCHAR(100),
    idMarcaComida INT,
    idColonia INT NOT NULL,
    idVoluntario INT NOT NULL,
    CONSTRAINT FK_trabajo_marcacomida
        FOREIGN KEY (idMarcaComida) REFERENCES MARCACOMIDA(idMarcaComida),
    CONSTRAINT FK_trabajo_colonia
        FOREIGN KEY (idColonia) REFERENCES COLONIA_FELINA(idColonia),
    CONSTRAINT FK_trabajo_voluntario
        FOREIGN KEY (idVoluntario) REFERENCES VOLUNTARIO(idVoluntario)
);

-- ============================================================
-- 17. CENTRO_VETERINARIO
-- ============================================================
CREATE TABLE CENTRO_VETERINARIO(
    idCentroVet INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200),
    mail VARCHAR(200),
    telefono VARCHAR(100),
    direccion VARCHAR(300)
);

-- ============================================================
-- 18. TIPO
-- ============================================================
CREATE TABLE TIPO(
    idTipo INT PRIMARY KEY AUTO_INCREMENT,
    tipoCampaña VARCHAR(200),
    tipoVacuna VARCHAR(200)
);

-- ============================================================
-- 19. CAMPAÑA_INTERVENCION
-- ============================================================
CREATE TABLE CAMPAÑA_INTERVENCION(
    idCampaña INT PRIMARY KEY AUTO_INCREMENT,
    fechaInicio DATE,
    fechaFin DATE,
    descripcion TEXT,
    idCentroVet INT NOT NULL,
    idColonia INT NOT NULL,
    idTipo INT NOT NULL,
    CONSTRAINT FK_campaña_centroveterinario
        FOREIGN KEY (idCentroVet) REFERENCES CENTRO_VETERINARIO(idCentroVet),
    CONSTRAINT FK_campaña_colonia
        FOREIGN KEY (idColonia) REFERENCES COLONIA_FELINA(idColonia),
    CONSTRAINT FK_campaña_tipo
        FOREIGN KEY (idTipo) REFERENCES TIPO(idTipo)
);

-- ============================================================
-- 20. PROFESIONAL
-- ============================================================
CREATE TABLE PROFESIONAL(
    idProfesional INT PRIMARY KEY AUTO_INCREMENT,
    activo BOOLEAN DEFAULT TRUE,
    idPersona INT NOT NULL,
    idCentroVet INT NOT NULL,
    CONSTRAINT FK_profesional_persona
        FOREIGN KEY (idPersona) REFERENCES PERSONA(idPersona),
    CONSTRAINT FK_profesional_centroveterinario
        FOREIGN KEY (idCentroVet) REFERENCES CENTRO_VETERINARIO(idCentroVet)
);

-- ============================================================
-- 21. PARTICIPA
-- ============================================================
CREATE TABLE PARTICIPA(
    idParticipa INT PRIMARY KEY AUTO_INCREMENT,
    idCampaña INT NOT NULL,
    idProfesional INT NOT NULL,
    CONSTRAINT FK_participa_campaña
        FOREIGN KEY (idCampaña) REFERENCES CAMPAÑA_INTERVENCION(idCampaña),
    CONSTRAINT FK_participa_profesional
        FOREIGN KEY (idProfesional) REFERENCES PROFESIONAL(idProfesional)
);

-- ============================================================
-- 22. ACCION_INDIVIDUAL
-- ============================================================
CREATE TABLE ACCION_INDIVIDUAL(
    idAccion INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    descripcion TEXT,
    autopsia TEXT,
    comentario TEXT,
    idGato INT NOT NULL,
    idProfesional INT NOT NULL,
    idCampaña INT,
    CONSTRAINT FK_accion_gato
        FOREIGN KEY (idGato) REFERENCES GATO(idGato),
    CONSTRAINT FK_accion_profesional
        FOREIGN KEY (idProfesional) REFERENCES PROFESIONAL(idProfesional),
    CONSTRAINT FK_accion_campaña
        FOREIGN KEY (idCampaña) REFERENCES CAMPAÑA_INTERVENCION(idCampaña)
);

-- ============================================================
-- 23. COMENTARIO
-- ============================================================
CREATE TABLE COMENTARIO(
    idComentario INT PRIMARY KEY AUTO_INCREMENT,
    contenidoComentario TEXT,
    idColonia INT NOT NULL,
    CONSTRAINT FK_comentario_colonia
        FOREIGN KEY (idColonia) REFERENCES COLONIA_FELINA(idColonia)
);

-- ============================================================
-- 24. ALBIRAMENT
-- ============================================================
CREATE TABLE ALBIRAMENT(
    idAlbirament INT PRIMARY KEY AUTO_INCREMENT,
    fechaVista DATE,
    idGato INT NOT NULL,
    idColonia INT NOT NULL,
    CONSTRAINT FK_albirament_gato
        FOREIGN KEY (idGato) REFERENCES GATO(idGato),
    CONSTRAINT FK_albirament_colonia
        FOREIGN KEY (idColonia) REFERENCES COLONIA_FELINA(idColonia)
);

-- ============================================================
-- Añadir la FK pendiente para cerrar el ciclo (GRUPO_TRABAJO -> VOLUNTARIO)
-- ESTO SE AÑADE AQUÍ PORQUE SI NO TENEMOS QUE GRUPO_TRABAJO TIENE FK A VOLUNTARIO
-- Y VOLUNTARIO TIENE FK A GRUPO_TRABAJO, Y NO SE PUEDE CREAR NINGUNA DE LAS DOS TABLAS
-- PORQUE DEPENDEN LA UNA DE LA OTRA. 
-- ============================================================
ALTER TABLE GRUPO_TRABAJO
ADD CONSTRAINT FK_grupotrabajo_responsable
    FOREIGN KEY (idResponsable) REFERENCES VOLUNTARIO(idVoluntario);
