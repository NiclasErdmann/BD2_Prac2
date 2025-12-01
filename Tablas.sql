/*
MODELO RELACIONAL

COLONIAFELINA(#idColonia, nombre, descripción, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo)

GATO(#idGato, numXIP, descripción, foto, idCementerio)

HISTORIAL(#idHistorial, fechaLLegada, fechaIda, idGato, idColonia)

INCIDENCIAGATO(#idIncidencia, fecha, descripción, tipo, idVoluntario)

CEMENTERIO(#idCementerio, nombre, ubicación)

VOLUNTARIO(email, telefono, 

AYUNTAMIENTO(#idAyuntamiento, nombre)

GRUPO DE TRABAJO(#idGrupoTrabajo, nombre, descripción, idAyuntamiento)

ROL(#idRol, nombre)

PUEDEHACER(#idPuedeHacer, idRol, Funciones)

FUNCIONES(#idFunciones, nombre)

PERSONA(#idPersona, nombre, apellido, usuario, contraseña, 

TRABAJO(#idTrabajo, descripción, fecha, hora, estado, idMarcaComida, idColonia, idVoluntario)

MARCACOMIDA(#idMarcaComida, nombre, calidad, caracteristicas)

CAMPAÑAINT(#idCampaña, fechaInicio, fechaFin, descripción, centroVeterinario, idColonia, 

TIPO (#idTipo, tipoCampaña, tipoVacuna )	– tipoVacuna puede ser NULL si el tipoCampaña no es vacunación –

PARTICIPA(#idParticipa, idCampaña, idProfesional)

PROFESIONAL(#idProfesional, activo(borrado logico), ...., idPersona, idCentroVet)

CENTROVETERINARIO(#idCentroVet, nombre, mail, telefono, dirección)

ACCIÓNINDIVIDUAL(#idAccion, fecha, descripción, autopsia, comentario, idGato, idProfesional)

COMENTARIO(#idComentario, ????
*/


/*//////////////////////////////////////
 he dejado q gpt nos redacte las tablas pa empezar. noo estan acabadas asi
/////////////////////////////////////// */

-- -------------------------
-- AYUNTAMIENTO
-- -------------------------
CREATE TABLE Ayuntamiento (
    idAyuntamiento INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- -------------------------
-- GRUPO DE TRABAJO
-- -------------------------
CREATE TABLE GrupoTrabajo (
    idGrupoTrabajo INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    idAyuntamiento INT NOT NULL,
    FOREIGN KEY (idAyuntamiento) REFERENCES Ayuntamiento(idAyuntamiento)
);

-- -------------------------
-- COLONIAFELINA
-- -------------------------
CREATE TABLE ColoniaFelina (
    idColonia INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    coordenadas VARCHAR(200),
    lugarReferencia VARCHAR(200),
    numeroGatos INT,
    idGrupoTrabajo INT NOT NULL,
    FOREIGN KEY (idGrupoTrabajo) REFERENCES GrupoTrabajo(idGrupoTrabajo)
);

-- -------------------------
-- CEMENTERIO
-- -------------------------
CREATE TABLE Cementerio (
    idCementerio INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(200)
);

-- -------------------------
-- GATO
-- -------------------------
CREATE TABLE Gato (
    idGato INT PRIMARY KEY,
    numXIP VARCHAR(50),
    descripcion TEXT,
    foto VARCHAR(255),
    idCementerio INT,
    FOREIGN KEY (idCementerio) REFERENCES Cementerio(idCementerio)
);

-- -------------------------
-- HISTORIAL
-- -------------------------
CREATE TABLE Historial (
    idHistorial INT PRIMARY KEY,
    fechaLlegada DATE NOT NULL,
    fechaIda DATE,
    idGato INT NOT NULL,
    idColonia INT NOT NULL,
    FOREIGN KEY (idGato) REFERENCES Gato(idGato),
    FOREIGN KEY (idColonia) REFERENCES ColoniaFelina(idColonia)
);

-- -------------------------
-- VOLUNTARIO
-- -------------------------
CREATE TABLE Voluntario (
    idVoluntario INT PRIMARY KEY,
    email VARCHAR(100),
    telefono VARCHAR(30)
);

-- -------------------------
-- INCIDENCIAGATO
-- -------------------------
CREATE TABLE IncidenciaGato (
    idIncidencia INT PRIMARY KEY,
    fecha DATE NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(50),
    idVoluntario INT,
    FOREIGN KEY (idVoluntario) REFERENCES Voluntario(idVoluntario)
);

-- -------------------------
-- ROL
-- -------------------------
CREATE TABLE Rol (
    idRol INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- -------------------------
-- FUNCIONES
-- -------------------------
CREATE TABLE Funciones (
    idFunciones INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- -------------------------
-- PUEDEHACER (Relación Rol - Funciones)
-- -------------------------
CREATE TABLE PuedeHacer (
    idPuedeHacer INT PRIMARY KEY,
    idRol INT NOT NULL,
    idFunciones INT NOT NULL,
    FOREIGN KEY (idRol) REFERENCES Rol(idRol),
    FOREIGN KEY (idFunciones) REFERENCES Funciones(idFunciones)
);

-- -------------------------
-- PERSONA
-- -------------------------
CREATE TABLE Persona (
    idPersona INT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    usuario VARCHAR(100),
    contrasena VARCHAR(100)   -- Consider hashing!
);

-- -------------------------
-- MARCACOMIDA
-- -------------------------
CREATE TABLE MarcaComida (
    idMarcaComida INT PRIMARY KEY,
    nombre VARCHAR(100),
    calidad VARCHAR(50),
    caracteristicas TEXT
);

-- -------------------------
-- TRABAJO
-- -------------------------
CREATE TABLE Trabajo (
    idTrabajo INT PRIMARY KEY,
    descripcion TEXT,
    fecha DATE,
    hora TIME,
    estado VARCHAR(50),
    idMarcaComida INT,
    idColonia INT NOT NULL,
    idVoluntario INT,
    FOREIGN KEY (idMarcaComida) REFERENCES MarcaComida(idMarcaComida),
    FOREIGN KEY (idColonia) REFERENCES ColoniaFelina(idColonia),
    FOREIGN KEY (idVoluntario) REFERENCES Voluntario(idVoluntario)
);

-- -------------------------
-- CENTRO VETERINARIO
-- -------------------------
CREATE TABLE CentroVeterinario (
    idCentroVet INT PRIMARY KEY,
    nombre VARCHAR(100),
    mail VARCHAR(100),
    telefono VARCHAR(30),
    direccion VARCHAR(200)
);

-- -------------------------
-- PROFESIONAL
-- -------------------------
CREATE TABLE Profesional (
    idProfesional INT PRIMARY KEY,
    activo BOOLEAN DEFAULT TRUE, -- borrado lógico
    idPersona INT NOT NULL,
    idCentroVet INT NOT NULL,
    FOREIGN KEY (idPersona) REFERENCES Persona(idPersona),
    FOREIGN KEY (idCentroVet) REFERENCES CentroVeterinario(idCentroVet)
);

-- -------------------------
-- TIPO (de campaña)
-- -------------------------
CREATE TABLE Tipo (
    idTipo INT PRIMARY KEY,
    tipoCampaña VARCHAR(100) NOT NULL,
    tipoVacuna VARCHAR(100)   -- puede ser NULL
);

-- -------------------------
-- CAMPAÑAINT
-- -------------------------
CREATE TABLE CampañaInt (
    idCampaña INT PRIMARY KEY,
    fechaInicio DATE,
    fechaFin DATE,
    descripcion TEXT,
    centroVeterinario VARCHAR(100),  -- ??? or FK? (not specified)
    idColonia INT,
    idTipo INT,                      -- assumed link
    FOREIGN KEY (idColonia) REFERENCES ColoniaFelina(idColonia),
    FOREIGN KEY (idTipo) REFERENCES Tipo(idTipo)
);

-- -------------------------
-- PROFESIONAL PARTICIPA EN CAMPAÑA
-- -------------------------
CREATE TABLE Participa (
    idParticipa INT PRIMARY KEY,
    idCampaña INT NOT NULL,
    idProfesional INT NOT NULL,
    FOREIGN KEY (idCampaña) REFERENCES CampañaInt(idCampaña),
    FOREIGN KEY (idProfesional) REFERENCES Profesional(idProfesional)
);

-- -------------------------
-- ACCIÓN INDIVIDUAL
-- -------------------------
CREATE TABLE AccionIndividual (
    idAccion INT PRIMARY KEY,
    fecha DATE,
    descripcion TEXT,
    autopsia BOOLEAN,
    comentario TEXT,
    idGato INT NOT NULL,
    idProfesional INT NOT NULL,
    FOREIGN KEY (idGato) REFERENCES Gato(idGato),
    FOREIGN KEY (idProfesional) REFERENCES Profesional(idProfesional)
);

-- -------------------------
-- COMENTARIO
-- -------------------------
CREATE TABLE Comentario (
    idComentario INT PRIMARY KEY
    -- ??? no attributes provided
);


