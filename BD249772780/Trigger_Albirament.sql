-- Trigger para actualizar el HISTORIAL automáticamente cuando se registra un ALBIRAMENT

DELIMITER //

CREATE TRIGGER tr_ActualizarHistorial_Albirament
BEFORE INSERT ON ALBIRAMENT
FOR EACH ROW
BEGIN
    -- 1. Cerrar el historial anterior del gato (poner fechaIda)
    UPDATE HISTORIAL 
    SET fechaIda = NEW.fechaVista 
    WHERE idGato = NEW.idGato 
    AND fechaIda IS NULL;
    
    -- 2. Crear nuevo registro en HISTORIAL con la nueva colonia
    INSERT INTO HISTORIAL (fechaLlegada, fechaIda, idGato, idColonia) 
    VALUES (NEW.fechaVista, NULL, NEW.idGato, NEW.idColonia);
END//

DELIMITER ;
