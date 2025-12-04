-- ===========================
--              ROL
-- ===========================
CREATE TABLE Rol (
    idRol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100)
);

-- ===========================
--           FUNCIONES
-- ===========================
CREATE TABLE Funciones (
    idFunciones INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100)
);

-- ===========================
--         PUEDE HACER
-- ===========================
CREATE TABLE PuedeHacer (
    idPuedeHacer INT PRIMARY KEY AUTO_INCREMENT,
    idRol INT NOT NULL,
    idFunciones INT NOT NULL,
    CONSTRAINT FK_PuedeHacer_Rol
        FOREIGN KEY (idRol) REFERENCES Rol(idRol),
    CONSTRAINT FK_PuedeHacer_Funciones
        FOREIGN KEY (idFunciones) REFERENCES Funciones(idFunciones)
);

-- ===========================
--           PERSONA
-- ===========================
CREATE TABLE Persona (
    idPersona INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    usuario VARCHAR(60) UNIQUE,
    contraseña VARCHAR(200),
    idRol INT,
    CONSTRAINT FK_Persona_Rol
        FOREIGN KEY (idRol) REFERENCES Rol(idRol)
);

-- ===========================
--        AYUNTAMIENTO
-- ===========================
CREATE TABLE Ayuntamiento (
    idAyuntamiento INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL
);

-- ===========================
--      GRUPO DE TRABAJO
-- ===========================
CREATE TABLE GrupoTrabajo (
    idGrupoTrabajo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    idAyuntamiento INT NOT NULL,
    -- idResponsable INT, -- hace referencia a un voluntario
    CONSTRAINT FK_GrupoTrabajo_Ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES Ayuntamiento(idAyuntamiento)
    -- CONSTRAINT FK_GrupoTrabajo_Voluntario
    --    FOREIGN KEY (idResponsable) REFERENCES Voluntario(idVoluntario)
);

-- ===========================
--         VOLUNTARIO
-- ===========================
CREATE TABLE Voluntario (
    idVoluntario INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) UNIQUE,
    telefono VARCHAR(20),
    idGrupoTrabajo INT,
    idAyuntamiento INT NOT NULL,
    idPersona INT NOT NULL,
    CONSTRAINT FK_Voluntario_GrupoTrabajo
        FOREIGN KEY (idGrupoTrabajo) REFERENCES GrupoTrabajo(idGrupoTrabajo),
    CONSTRAINT FK_Voluntario_Ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES Ayuntamiento(idAyuntamiento),
    CONSTRAINT FK_ColoniaFelina_Persona
        FOREIGN KEY (idPersona) REFERENCES Persona(idPersona)
);


-- ===========================
--       COLONIA FELINA
-- ===========================
CREATE TABLE ColoniaFelina (
    idColoniaFelina INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    coordenadas VARCHAR(255),
    lugarReferencia VARCHAR(255),
    numeroGatos INT,
    idGrupoTrabajo INT,
    idAyuntamiento INT NOT NULL,
    CONSTRAINT FK_ColoniaFelina_GrupoTrabajo
        FOREIGN KEY (idGrupoTrabajo) REFERENCES GrupoTrabajo(idGrupoTrabajo),
    CONSTRAINT FK_ColoniaFelina_Ayuntamiento
        FOREIGN KEY (idAyuntamiento) REFERENCES Ayuntamiento(idAyuntamiento)
);

-- ===========================
--         CEMENTERIO
-- ===========================
CREATE TABLE Cementerio (
    idCementerio INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(200)
);

-- ===========================
--            GATO
-- ===========================
CREATE TABLE Gato (
    idGato INT PRIMARY KEY AUTO_INCREMENT,
    numXIP VARCHAR(50),
    descripcion TEXT,
    foto LONGBLOB,
    idCementerio INT,
    CONSTRAINT FK_Gato_Cementerio
        FOREIGN KEY (idCementerio) REFERENCES Cementerio(idCementerio)
);

-- ===========================
--          HISTORIAL
-- ===========================
CREATE TABLE Historial (
    idHistorial INT PRIMARY KEY AUTO_INCREMENT,
    fechaLlegada DATE,
    fechaIda DATE,
    idGato INT NOT NULL,
    idColoniaFelina INT NOT NULL,
    CONSTRAINT FK_Historial_Gato
        FOREIGN KEY (idGato) REFERENCES Gato(idGato),
    CONSTRAINT FK_Historial_ColoniaFelina
        FOREIGN KEY (idColoniaFelina) REFERENCES ColoniaFelina(idColoniaFelina)
);



-- ===========================
--       INCIDENCIA GATO
-- ===========================
CREATE TABLE IncidenciaGato (
    idIncidencia INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(100),
    idVoluntario INT NOT NULL, -- el que registra la incidencia
    idGato INT, -- puede ser de un gato o no (deconocido o incidencia de una colonia)
    idColoniaFelina INt NOT NULL,
    CONSTRAINT FK_IncidenciaGato_Voluntario
        FOREIGN KEY (idVoluntario) REFERENCES Voluntario(idVoluntario),
    CONSTRAINT FK_IncidenciaGato_Gato
        FOREIGN KEY (idGato) REFERENCES Gato(idGato),
    CONSTRAINT FK_IncidenciaGato_ColoniaFelina
        FOREIGN KEY (idColoniaFelina) REFERENCES ColoniaFelina(idColoniaFelina)
);

-- ===========================
--        MARCA COMIDA
-- ===========================
CREATE TABLE MarcaComida (
    idMarcaComida INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    calidad VARCHAR(100),
    caracteristicas TEXT
);

-- ===========================
--          TIPO (Campaña Intervenciaon)
-- ===========================
CREATE TABLE Tipo (
    idTipo INT PRIMARY KEY AUTO_INCREMENT,
    tipoCampaña VARCHAR(100) NOT NULL,
    tipoVacuna VARCHAR(100) NULL
);

-- ===========================
--      CENTRO VETERINARIO
-- ===========================
CREATE TABLE CentroVeterinario (
    idCentroVet INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150),
    mail VARCHAR(150),
    telefono VARCHAR(30),
    direccion VARCHAR(255)
);


-- ===========================
--         PROFESIONAL
-- ===========================
CREATE TABLE Profesional (
    idProfesional INT PRIMARY KEY AUTO_INCREMENT,
    activo BOOLEAN DEFAULT TRUE,
    idPersona INT NOT NULL,
    idCentroVet INT NOt NULL,
    CONSTRAINT FK_Profesional_Persona
        FOREIGN KEY (idPersona) REFERENCES Persona(idPersona),
    CONSTRAINT FK_Profesional_CentroVeterinario
        FOREIGN KEY (idCentroVet) REFERENCES CentroVeterinario(idCentroVet)
);

-- ===========================
--       CAMPAÑA INTTERVENCION
-- ===========================
CREATE TABLE CampanyaInternevion (
    idCampanya INT PRIMARY KEY AUTO_INCREMENT,
    fechaInicio DATE,
    fechaFin DATE,
    descripcion TEXT,
    centroVeterinario VARCHAR(200),
    idColoniaFelina INT NOT NULL,
    idTipo INT NOT NULL,
    idCentroVet INT NOT NULL,
    CONSTRAINT FK_CampanyaInternevion_ColoniaFelina
        FOREIGN KEY (idColoniaFelina) REFERENCES ColoniaFelina(idColoniaFelina),
    CONSTRAINT FK_CampanyaInternevion_Tipo
        FOREIGN KEY (idTipo) REFERENCES Tipo(idTipo),
    CONSTRAINT FK_CampanyaInternevion_CentroVeterinario
        FOREIGN KEY (idCentroVet) REFERENCES CentroVeterinario(idCentroVet)
);

-- ===========================
--          PARTICIPA
-- ===========================
CREATE TABLE Participa (
    idParticipa INT PRIMARY KEY AUTO_INCREMENT,
    idCampanya INT NOT NULL,
    idProfesional INT NOT NULL,
    CONSTRAINT FK_Participa_CampanyaInternevion
        FOREIGN KEY (idCampanya) REFERENCES CampanyaInternevion(idCampanya),
    CONSTRAINT FK_Participa_Profesional
        FOREIGN KEY (idProfesional) REFERENCES Profesional(idProfesional)
);

-- ===========================
--            TRABAJO
-- revisar
-- ===========================
CREATE TABLE Trabajo (
    idTrabajo INT PRIMARY KEY AUTO_INCREMENT,
    descripcion TEXT,
    fecha DATE,
    hora TIME,
    estado VARCHAR(50),
    idMarcaComida INT,
    idColoniaFelina INT,
    idVoluntario INT,
    idGato INT,
    idProfesional INT,
    CONSTRAINT FK_Trabajo_MarcaComida
        FOREIGN KEY (idMarcaComida) REFERENCES MarcaComida(idMarcaComida),
    CONSTRAINT FK_Trabajo_ColoniaFelina
        FOREIGN KEY (idColoniaFelina) REFERENCES ColoniaFelina(idColoniaFelina),
    CONSTRAINT FK_Trabajo_Voluntario
        FOREIGN KEY (idVoluntario) REFERENCES Voluntario(idVoluntario),
    CONSTRAINT FK_AccionIndividual_Gato
        FOREIGN KEY (idGato) REFERENCES Gato(idGato),
    CONSTRAINT FK_AccionIndividual_Profesional
        FOREIGN KEY (idProfesional) REFERENCES Profesional(idProfesional)
);

-- ===========================
--         COMENTARIO
-- ===========================
CREATE TABLE Comentario (
    idComentario INT PRIMARY KEY AUTO_INCREMENT,
    contenidoComentario TEXT,
    idCampanya INT NOT NULL,
    CONSTRAINT FK_Comentario_CampanyaInternevion
        FOREIGN KEY (idCampanya) REFERENCES CampanyaInternevion(idCampanya)
);
