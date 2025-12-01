# BD2_Prac2
gatoos


Las FKs se nombran con el siguiente estandard:
FK_Tabla1_Tabla2

CREATE TABLE GrupoTrabajo (
    idGrupoTrabajo INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    idAyuntamiento INT NOT NULL,
    ADD CONSTRAINT FK_GrupTrabajo_Ayuntamiento
    FOREIGN KEY (idAyuntamiento) REFERENCES Ayuntamiento(idAyuntamiento)
);