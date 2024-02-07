<?php

date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
// Ruta GET para obtener los resultados filtrados
$app->get('/api/stock/datatables/movimiento-de-stock', function (Request $request, Response $response) {
    // Obtener los parámetros GET
    $queryParams = $request->getQueryParams();
    // Construir la consulta SQL con los filtros
    $sql = "SELECT mlog.id_mlog, CASE WHEN mlog.op_mlog = '1' THEN 'entrada' WHEN mlog.op_mlog = '-1' THEN 'salida' ELSE '' END AS operacion, pp.producto as prod_nombre, p.idmaq, qr.qr_code, dep1.dep_nombre AS dep1_nombre, dep1.dep_ciudad AS dep1_ciudad, uremitente.id_usu AS dep1_idusu, CONCAT(uremitente.nombre_usu, ' ', uremitente.apellido_usu) AS dep1_agente, i.movi_id, dep2.dep_nombre AS dep2_nombre, dep2.dep_ciudad AS dep2_ciudad, ureceptor.id_usu AS dep2_idusu, CONCAT(ureceptor.nombre_usu, ' ', ureceptor.apellido_usu) AS dep2_agente, mlog.fechahora_mlog, mlog.op_mlog, mlog.iddep1_mlog, mlog.iddep2_mlog FROM log_movstock AS mlog LEFT JOIN stock_mov_items AS i ON i.movi_id = mlog.idimov_mlog LEFT JOIN stock_mov AS m ON i.movi_idmov = m.mov_id INNER JOIN stock_depositos AS dep1 ON dep1.id_dep = mlog.iddep1_mlog INNER JOIN stock_depositos AS dep2 ON dep2.id_dep = mlog.iddep2_mlog INNER JOIN stock_usuarios AS uremitente ON uremitente.id_usu = m.mov_idusu INNER JOIN stock_usuarios AS ureceptor ON ureceptor.id_usu = mlog.idusu_mlog LEFT JOIN stock_qrcode AS qr ON qr.qr_code = mlog.qr_mlog LEFT JOIN app_productos AS p ON p.id_qr = qr.id_qrcode LEFT JOIN productos AS pp ON pp.`id_producto` = p.idproducto WHERE 1=1 ";
    
    // Construir los filtros en la consulta SQL
    foreach ($queryParams as $key => $value) {
        if ($key === 'fechahora_mlog_start') {
            $sql .= "AND DATE(mlog.fechahora_mlog) >= :fechahora_mlog_start ";
        } elseif ($key === 'fechahora_mlog_end') {
            $sql .= "AND DATE(mlog.fechahora_mlog) <= :fechahora_mlog_end ";
        } elseif ($key === 'dep1_idusu') {
            $ids = explode(',', $value);
            $placeholders = implode(',', array_fill(0, count($ids), ":$key"));
            if ($key === 'dep1_idusu') {
                $key = 'uremitente.id_usu';
            }
            $sql .= "AND $key IN ($placeholders) ";
            } else {
            $sql .= "AND $key = :$key ";
        }
    }
    
    // Crear una conexión a la base de datos
    $db = $this->db;
    $stmt = $db->prepare($sql);

    // Asignar los valores de los filtros
    foreach ($queryParams as $key => $value) {
        if ($key === 'fechahora_mlog_start' || $key === 'fechahora_mlog_end') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        } elseif ($key === 'dep1_idusu') {
            $ids = explode(',', $value);
            $stmt->bindValue(":$key", $ids, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(":$key", $value);
        }
    }


    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cerrar la conexión a la base de datos
    $pdo = null;

    // Devolver los resultados como JSON
    $response->getBody()->write(json_encode($results));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
