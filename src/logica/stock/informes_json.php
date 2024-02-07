<?php

date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

// Ruta para obtener el JSON
$app->get('/api/stock/json/maq-estado-semana', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos desde $this->db
    $db = $this->db;

    // Consultar los datos de las últimas dos semanas
    $query  = "SELECT id, operativo, disponible, transito, reparacion, total, fecha, semana FROM inf_estadomaqxsemana ORDER BY id DESC LIMIT 2";
    $result = $db->query($query);

    // Obtener los datos de la última semana y la semana anterior
    $lastWeekData     = $result->fetch(PDO::FETCH_ASSOC);
    $previousWeekData = $result->fetch(PDO::FETCH_ASSOC);

    // Calcular el porcentaje de diferencia entre las dos semanas
    $porcentajeOperativasAnterior = ($lastWeekData['operativo'] - $previousWeekData['operativo']) / $previousWeekData['operativo'] * 100;
    $resultadoOperativas          = ($porcentajeOperativasAnterior < 0) ? 'negativo' : 'positivo';

    $porcentajeDisponiblesAnterior = ($lastWeekData['disponible'] - $previousWeekData['disponible']) / $previousWeekData['disponible'] * 100;
    $resultadoDisponibles          = ($porcentajeDisponiblesAnterior < 0) ? 'negativo' : 'positivo';

    $porcentajeTransitoAnterior = ($lastWeekData['transito'] - $previousWeekData['transito']) / $previousWeekData['transito'] * 100;
    $resultadoTransito          = ($porcentajeTransitoAnterior < 0) ? 'negativo' : 'positivo';

    $porcentajeReparacionAnterior = ($lastWeekData['reparacion'] - $previousWeekData['reparacion']) / $previousWeekData['reparacion'] * 100;
    $resultadoReparacion          = ($porcentajeReparacionAnterior < 0) ? 'negativo' : 'positivo';

    $porcentajeTotalAnterior = ($lastWeekData['total'] - $previousWeekData['total']) / $previousWeekData['total'] * 100;
    $resultadoTotal          = ($porcentajeTotalAnterior < 0) ? 'negativo' : 'positivo';

    // Estructurar los datos en el formato deseado
    $data = [
        'operativas'  => [
            [
                'operativashoy'  => $lastWeekData['operativo'],
                'semanaanterior' => round($porcentajeOperativasAnterior, 2) . '%',
                'resultado'      => $resultadoOperativas,
            ],
        ],
        'disponibles' => [
            [
                'disponibleshoy' => $lastWeekData['disponible'],
                'semanaanterior' => round($porcentajeDisponiblesAnterior, 2) . '%',
                'resultado'      => $resultadoDisponibles,
            ],
        ],
        'transito'    => [
            [
                'transitohoy'    => $lastWeekData['transito'],
                'semanaanterior' => round($porcentajeTransitoAnterior, 2) . '%',
                'resultado'      => $resultadoTransito,
            ],
        ],
        'reparacion'  => [
            [
                'reparacionhoy'  => $lastWeekData['reparacion'],
                'semanaanterior' => round($porcentajeReparacionAnterior, 2) . '%',
                'resultado'      => $resultadoReparacion,
            ],
        ],
        'total'       => [
            'totalhoy'       => $lastWeekData['total'],
            'semanaanterior' => round($porcentajeTotalAnterior, 2) . '%',
            'resultado'      => $resultadoTotal,
        ],
    ];

    // Configurar la respuesta HTTP con el JSON generado
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

    return $response;
});

// Ruta para generar el archivo JSON y crear los gráficos
$app->get('/api/stock/json/estado-maq-semana', function ($request, $response) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Consulta SQL para obtener los datos de las últimas 7 semanas ordenadas por semana de mayor a menor
    $sql = "SELECT operativo, disponible, transito, reparacion, total, fecha, semana
            FROM inf_estadomaqxsemana
            ORDER BY semana DESC
            LIMIT 7";

    // Ejecutar la consulta SQL y obtener los resultados
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear arrays para almacenar los datos de cada estado
    $operativasData  = [];
    $disponiblesData = [];
    $transitoData    = [];
    $reparacionData  = [];

    // Recorrer los datos y almacenarlos en los arrays correspondientes
    foreach ($data as $row) {
        $week                   = 'Semana ' . $row['semana'];
        $operativasData[$week]  = $row['operativo'];
        $disponiblesData[$week] = $row['disponible'];
        $transitoData[$week]    = $row['transito'];
        $reparacionData[$week]  = $row['reparacion'];
    }

    // Crear un array con los datos y etiquetas necesarios para cada gráfico
    $operativasChart = [
        'selector' => 'canvas_operativas',
        'bgColor'  => '#7261A320',
        'hBgColor' => '#7261A3',
        'label'    => 'Operativas',
        'data'     => $operativasData,
    ];

    $disponiblesChart = [
        'selector' => 'canvas_disponibles',
        'bgColor'  => '#7261A320',
        'hBgColor' => '#7261A3',
        'label'    => 'Disponibles',
        'data'     => $disponiblesData,
    ];

    $transitoChart = [
        'selector' => 'canvas_transito',
        'bgColor'  => '#20C99720',
        'hBgColor' => '#20C997',
        'label'    => 'Transito',
        'data'     => $transitoData,
    ];

    $reparacionChart = [
        'selector' => 'canvas_reparacion',
        'bgColor'  => '#2C99FF20',
        'hBgColor' => '#2C99FF',
        'label'    => 'Reparacion',
        'data'     => $reparacionData,
    ];

    // Crear un array que contenga los datos para todos los gráficos
    $chartsData = [
        $operativasChart,
        $disponiblesChart,
        $transitoChart,
        $reparacionChart,
    ];

    // Ordenar el array $chartsData por semana de menor a mayor
    usort($chartsData, function ($a, $b) {
        $aWeek = array_keys($a['data'])[0];
        $bWeek = array_keys($b['data'])[0];
        return strcmp($aWeek, $bWeek);
    });

    // Crear un archivo JSON con los datos de los gráficos
    $json = json_encode($chartsData);

    // Establecer la cabecera de respuesta para indicar que se enviará un archivo JSON
    $response = $response->withHeader('Content-Type', 'application/json');

    // Devolver el archivo JSON como respuesta
    $response->getBody()->write($json);

    return $response;
});

$app->get('/api/stock/json/tipo-maq-estado', function (Request $request, Response $response) {
    $db = $this->db;

    // Consulta para contar la cantidad de máquinas en cada estado de producto
    $query = "SELECT p.estado_producto, pp.producto as prod_nombre, COUNT(*) AS cantidad
              FROM app_productos p
              LEFT JOIN productos pp ON p.idproducto = pp.`id_producto`
              WHERE p.estado_producto IN ('0', '1', '2', '3')
              AND pp.tipoagrupacion = '1'
              GROUP BY p.estado_producto, pp.producto as prod_nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = array();
    foreach ($results as $row) {
        switch ($row['estado_producto']) {
            case '0':
                $estado = 'Disponibles';
                break;
            case '1':
                $estado = 'Operativas';
                break;
            case '2':
                $estado = 'Transito';
                break;
            case '3':
                $estado = 'Reparacion';
                break;
            default:
                $estado = 'Desconocido';
                break;
        }
        $jsonResponse[$estado][] = array(
            'producto as prod_nombre' => $row['producto as prod_nombre'],
            'cantidad'    => $row['cantidad'],
        );
    }

    $response->getBody()->write(json_encode($jsonResponse));
    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/api/stock/json/top-10-productos', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND pp.tipoagrupacion = '0'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/top-10-productos-mensual', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND MONTH(log.fechahora_mlog) = MONTH(CURDATE())
    AND pp.tipoagrupacion = '0'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/top-10-productos-semanal', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND WEEK(log.fechahora_mlog) = WEEK(CURDATE())
    AND pp.tipoagrupacion = '0'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

/* Comienza top 10 movimiento de maquinas */

$app->get('/api/stock/json/top-10-maquinas-anual', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND pp.tipoagrupacion = '1'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/top-10-maquinas-mensual', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND MONTH(log.fechahora_mlog) = MONTH(CURDATE())
    AND pp.tipoagrupacion = '1'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/top-10-maquinas-semanal', function (Request $request, Response $response, $args) {
    // Obtener la conexión a la base de datos
    $db = $this->db;

    // Ejecutar la consulta
    $query = "SELECT
    pp.producto as prod_nombre AS producto,
    COUNT(p.idproducto) AS cantidad
FROM
    log_movstock log
    LEFT JOIN stock_qrcode qr ON qr.qr_code = log.qr_mlog
    LEFT JOIN app_productos p ON p.id_qr = qr.id_qrcode
    LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
WHERE
    log.op_mlog = '-1'
    AND YEAR(log.fechahora_mlog) = YEAR(CURDATE())
    AND WEEK(log.fechahora_mlog) = WEEK(CURDATE())
    AND pp.tipoagrupacion = '1'
GROUP BY
    p.idproducto
ORDER BY
    COUNT(p.idproducto) DESC
LIMIT 10;";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

/* Comienza top 10 ubicacion de maquinas */

$app->get('/api/stock/json/cantidad-deposito', function (Request $request, Response $response) {
    // Establecer la conexión a la base de datos
    $db = $this->db;

    // Consulta para contar la cantidad de máquinas por depósito
    $query = "SELECT dep.dep_nombre AS deposito, COUNT(*) AS cantidad
    FROM app_productos p
    LEFT JOIN productos pp ON p.idproducto = pp.`id_producto`
    LEFT JOIN stock_depositos dep ON dep.id_dep = p.iddeposito
    WHERE pp.tipoagrupacion = '1' AND p.estado_producto IN ('0','1','2','3')
    GROUP BY p.iddeposito
    ORDER BY COUNT(*) DESC
    LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/cantidad-ciudad', function (Request $request, Response $response) {
    // Establecer la conexión a la base de datos
    $db = $this->db;

    // Consulta para contar la cantidad de máquinas por ciudad
    $query = "SELECT dep.dep_ciudad AS ciudad, COUNT(*) AS cantidad
    FROM app_productos p
    LEFT JOIN productos pp ON p.idproducto = pp.`id_producto`
    LEFT JOIN stock_depositos dep ON dep.id_dep = p.iddeposito
    WHERE pp.tipoagrupacion = '1' AND p.estado_producto IN ('0','1','2','3')
    GROUP BY dep.dep_ciudad
    ORDER BY COUNT(*) DESC
    LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/stock/json/cantidad-usuario', function (Request $request, Response $response) {
    // Establecer la conexión a la base de datos
    $db = $this->db;

    // Consulta para contar la cantidad de máquinas por usuario
    $query = "SELECT CONCAT(u.nombre_usu, ' ', u.apellido_usu) AS usuario, COUNT(*) AS cantidad
    FROM app_productos p
    LEFT JOIN productos pp ON p.idproducto = pp.`id_producto`
    LEFT JOIN stock_depositos dep ON dep.id_dep = p.iddeposito
    LEFT JOIN stock_usuarios u ON u.id_usu = dep.dep_idusu
    WHERE pp.tipoagrupacion = '1' AND p.estado_producto IN ('0','1','2','3')
    GROUP BY u.id_usu
    ORDER BY COUNT(*) DESC
    LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar el JSON de respuesta
    $jsonResponse = json_encode($results);

    // Configurar la respuesta con el JSON
    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');
});

// Ruta para obtener las máquinas inventariadas
$app->get('/api/stock/json/maquinas-inventariadas', function (Request $request, Response $response) use ($dsn, $username, $password) {
    try {
        // Conexión a la base de datos
        $db = $this->db;

        $query1 = "SELECT COUNT(*) as count FROM app_componentes left join app_productos p on p.id = app_componentes.idprodconjunto_compoapp where p.estado_producto in ('0','1','2','3') GROUP BY idprodconjunto_compoapp HAVING count > 4";
        $stmt1  = $db->prepare($query1);
        $stmt1->execute();
        $maquinasConMasDe4Registros = $stmt1->rowCount();

        // Consulta para obtener las máquinas con solo un registro en app_componentes
        $query2 = "SELECT COUNT(*) as count FROM app_componentes left join app_productos p on p.id = app_componentes.idprodconjunto_compoapp where p.estado_producto in ('0','1','2','3') GROUP BY idprodconjunto_compoapp HAVING count < 5";
        $stmt2  = $db->prepare($query2);
        $stmt2->execute();
        $maquinasConUnRegistro = $stmt2->rowCount();

        $porcentaje = round(($maquinasConMasDe4Registros / $maquinasConUnRegistro) * 100);
        // Crear el resultado JSON
        $result = [
            'inventariadas'            => $maquinasConMasDe4Registros,
            'no_inventariadas'         => $maquinasConUnRegistro,
            'porcentaje_inventariadas' => $porcentaje,
        ];

        // Devolver el resultado como JSON
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Manejar errores de la base de datos
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/api/stock/json/maq-reparacion-7-dias', function (Request $request, Response $response) {
    // Obtener la conexión a la base de datos
    $db = $this->db;
    
    // Calcular la fecha hace 7 días
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
    
    // Consulta SQL para obtener la cantidad total de máquinas en reparación
    $sqlTotal = "SELECT COUNT(*) as total FROM `log_estado` log 
                 LEFT JOIN app_productos p ON p.id = log.log_idprod 
                 LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto 
                 WHERE p.estado_producto = '3' AND log.log_estado = '3'";
    
    // Consulta SQL para obtener la cantidad de máquinas en reparación por más de 7 días
    $sqlSevenDays = "SELECT COUNT(*) as total FROM `log_estado` log 
                     LEFT JOIN app_productos p ON p.id = log.log_idprod 
                     LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto 
                     WHERE p.estado_producto = '3' AND log.log_estado = '3' 
                     AND log.log_fechahora <= :sevenDaysAgo";
    
    // Ejecutar la consulta para obtener la cantidad total de máquinas en reparación
    $stmtTotal = $db->prepare($sqlTotal);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $cantidadTotal = $resultTotal['total'];
    
    // Ejecutar la consulta para obtener la cantidad de máquinas en reparación por más de 7 días
    $stmtSevenDays = $db->prepare($sqlSevenDays);
    $stmtSevenDays->bindParam(':sevenDaysAgo', $sevenDaysAgo);
    $stmtSevenDays->execute();
    $resultSevenDays = $stmtSevenDays->fetch(PDO::FETCH_ASSOC);
    $cantidadSieteDias = $resultSevenDays['total'];
    
    // Calcular el porcentaje
    $porcentaje = ($cantidadSieteDias / $cantidadTotal) * 100;
    
    // Crear la respuesta en formato JSON
    $responseData = [
        'cantidad_total' => $cantidadTotal,
        'cantidad_siete_dias' => $cantidadSieteDias,
        'porcentaje' => $porcentaje
    ];
    
    // Enviar la respuesta en formato JSON
    $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($responseData));
    
    return $response;
});