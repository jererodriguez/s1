-- Obtener la fecha actual y el número de semana del año
SET @fecha = CURDATE();
SET @semana = WEEK(@fecha);

-- Verificar si la fecha ya existe en la tabla
SET @existe_fecha = (SELECT COUNT(*) FROM inf_estadomaqxsemana WHERE fecha = @fecha);

-- Insertar valores solo si la fecha no existe en la tabla
INSERT IGNORE INTO inf_estadomaqxsemana (operativo, disponible, transito, reparacion, total, fecha, semana)
SELECT
  SUM(CASE WHEN p.estado_producto = '1' THEN 1 ELSE 0 END),
  SUM(CASE WHEN p.estado_producto = '0' THEN 1 ELSE 0 END),
  SUM(CASE WHEN p.estado_producto = '2' THEN 1 ELSE 0 END),
  SUM(CASE WHEN p.estado_producto = '3' THEN 1 ELSE 0 END),
  SUM(CASE WHEN p.estado_producto IN ('1', '0', '2', '3') THEN 1 ELSE 0 END),
  @fecha,
  @semana
FROM app_productos p
LEFT JOIN stock_productos pp ON p.idproducto = pp.id
WHERE pp.tipoagrupacion = '1'
HAVING @existe_fecha = 0;

-- Mostrar los valores insertados
SELECT * FROM inf_estadomaqxsemana;

