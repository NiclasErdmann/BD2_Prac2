-- ayuntamiento nombre, apellido, usuario, contrasena, email, telefono
DELIMITER //
CREATE PROCEDURE procedure_anyade_voluntario(ayuntamiento VARCHAR(200), nombre VARCHAR(200), apellido VARCHAR(200), usuario VARCHAR(200), contrasena VARCHAR(200), email VARCHAR(200), telefono VARCHAR(100))
BEGIN
    DECLARE idA INT;
    DECLARE idP INT;

    START TRANSACTION;
        SELECT a.idAyuntamiento INTO idA
            FROM AYUNTAMIENTO a
            WHERE a.nombre = ayuntamiento;
            
        INSERT INTO PERSONA (nombre, apellido, usuario, contrasena, email, telefono) VALUES
        (nombre, apellido, usuario, contrasena, email, telefono);

        SELECT p.idPersona INTO idP
            FROM PERSONA p
            WHERE p.usuario = usuario;
        
        INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES
        (idA, NULL, idP);

        -- Commit the transaction if both operations succeed
    COMMIT;
END;
-- call procedure_anyade_voluntario ('Cuenca', 'Nic', 'Erd', 'nic.vol', 'con', 'mail', '32432')
