<?php

namespace Clases\Stock;

use \PDO;

class Informes
{


    public static function getCantMaqXEstado(PDO $db, string $fecha1, string $fecha2): array
    {
        // Validación de entrada
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha1) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha2)) {
            return array('code' => -1, 'status' => 'error', 'message' => 'Las fechas deben tener el formato "YYYY-MM-DD"');
        }
        
    
        // Consulta preparada con marcadores de posición
        $sql = "SELECT SUM(operativo) as operativo, SUM(disponible) as disponible, SUM(transito) as transito, SUM(reparacion) as reparacion, SUM(desechar) as desechar, SUM(total) as total, fecha FROM `inf_cantmaqxtipoyestado` WHERE fecha BETWEEN ? AND ? GROUP BY fecha ORDER BY fecha ASC;";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fecha1, $fecha2]);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    
        return array('code' => 1, 'status' => 'success', 'message' => 'La consulta se realizó exitosamente', 'data' => $data);
    }

    public static function putCantxagente(PDO $db): array
{
    $fecha = date('Y-m-d');

    $sql = "SELECT * FROM `inf_cantmaqxagente` WHERE fecha = '$fecha';";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_OBJ);

    if ($stmt->rowCount() == 0) {

        $sql = "SELECT
            dep.id_dep,
            u.id_usu,
            dep.dep_ciudad,
            CONCAT(u.nombre_usu, ' ', u.apellido_usu) AS agente,
            dep.dep_nombre,
            COUNT(p.id) AS cantidad,
            CURRENT_DATE AS fecha
            FROM
            `app_productos` p
            RIGHT JOIN stock_qrcode qr ON qr.id_qrcode = p.id_qr
            LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
            LEFT JOIN stock_depositos dep ON dep.id_dep = p.iddeposito
            LEFT JOIN stock_usuarios u ON u.id_usu = dep.dep_idusu
            WHERE
            p.id IS NOT NULL
            AND pp.producto as prod_nombre IS NOT NULL
            AND pp.`id_producto`_estado = '1'
            AND pp.tipoagrupacion = '1'
            AND qr.estado_qrcode = '1'
            AND p.estado_producto IN ('0','1','2','3')
            GROUP BY dep.id_dep ORDER BY dep_ciudad ASC, u.nombre_usu ASC, dep.dep_nombre ASC;";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $array = json_decode(json_encode($data), true);

        $sql = "INSERT INTO `inf_cantmaqxagente` (`id_dep`, `id_usu`, `dep_ciudad`, `agente`, `dep_nombre`, `cantidad`, `fecha`) VALUES (:id_dep, :id_usu, :dep_ciudad, :agente, :dep_nombre, :cantidad, :fecha);";
        $sentencia = $db->prepare($sql);
        foreach ($array as $fila) {
            $sentencia->execute($fila);
        }
        return array('code' => 1, 'status' => 'success', 'message' => '', 'data' => $data);
    } else {
        return array('code' => 1, 'status' => 'success', 'message' =>
            'Ya se cargó el informe de la fecha ' . $fecha, 'data' => '');
    }
}



    // public static function putCantxagente(pdo $db): array
    // {
    //     $fecha = date('Y-m-d');

    //     $sql  = "SELECT * FROM `inf_cantmaqxagente` where fecha = '" . $fecha . "';";
    //     $stmt = $db->prepare($sql);
    //     $stmt->execute();
    //     $data = $stmt->fetch(PDO::FETCH_OBJ);

    //     if ($stmt->rowCount() == 0) {

    //         $sql = "SELECT
    //         dep.id_dep,
    //         u.id_usu,
    //             dep.dep_ciudad,
    //             concat(u.nombre_usu, ' ', u.apellido_usu) as agente,
    //             dep.dep_nombre,
    //             count(p.id) as cantidad,
    //             CURRENT_DATE as fecha
    //         FROM
    //             `app_productos` p
    //             RIGHT JOIN stock_qrcode qr ON qr.id_qrcode = p.id_qr
    //             LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
    //             LEFT JOIN stock_depositos dep on dep.id_dep = p.iddeposito
    //             LEFT JOIN stock_usuarios u on u.id_usu = dep.dep_idusu
    //         WHERE
    //             p.id IS NOT NULL
    //             AND pp.producto as prod_nombre IS NOT NULL
    //             AND pp.`id_producto`_estado = '1'
    //             AND pp.tipoagrupacion = '1'
    //             AND qr.estado_qrcode = '1'
    //             and p.estado_producto in ('0','1','2','3')
    //         GROUP BY dep.id_dep ORDER BY dep_ciudad ASC, u.nombre_usu ASC, dep.dep_nombre ASC;";

    //         $stmt  = $db->query($sql);
    //         $data  = $stmt->fetchAll(PDO::FETCH_OBJ);
    //         $array = json_decode(json_encode($data), true);

    //         $sql       = "INSERT INTO `inf_cantmaqxagente` (`id_dep`, `id_usu`, `dep_ciudad`, `agente`, `dep_nombre`, `cantidad`, `fecha`) VALUES (:id_dep, :id_usu, :dep_ciudad, :agente, :dep_nombre, :cantidad, :fecha);";
    //         $sentencia = $db->prepare($sql);
    //         foreach ($array as $fila) {

    //             $sentencia->execute($fila);
    //         }
    //         return array('code' => 1, 'status' => 'success', 'message' => '', 'data' => $data);
    //     } else {
    //         return array('code' => 1, 'status' => 'success', 'message' =>
    //             'Ya se cargo el informe de la fecha ' . $fecha, 'data' => '');
    //     }

    // }

    
    
    public static function putCantxtipoyestado(pdo $db): array
    {
        $fecha = date('Y-m-d');

        $sql  = "SELECT * FROM `inf_cantmaqxtipoyestado` where fecha = '" . $fecha . "';";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($stmt->rowCount() == 0) {

            $sql = "SELECT
            pp.producto as prod_nombre,
            pp.prod_descrip,
            pp.prod_foto,
            p.idproducto,
            (SELECT COUNT(*) FROM app_productos p1 RIGHT JOIN stock_qrcode qr1 ON qr1.id_qrcode = p1.id_qr WHERE p1.id IS NOT NULL AND p1.estado_producto = '-1' AND qr1.estado_qrcode = '1' AND p1.idproducto = p.idproducto) AS desechar,
            (SELECT COUNT(*)  FROM app_productos p2 RIGHT JOIN stock_qrcode qr2 ON qr2.id_qrcode = p2.id_qr WHERE p2.id IS NOT NULL AND p2.estado_producto = '0' AND qr2.estado_qrcode = '1' AND p2.idproducto = p.idproducto) AS disponible,
            (SELECT COUNT(*)  FROM app_productos p3 RIGHT JOIN stock_qrcode qr3 ON qr3.id_qrcode = p3.id_qr WHERE p3.id IS NOT NULL AND p3.estado_producto = '1' AND qr3.estado_qrcode = '1' AND p3.idproducto = p.idproducto) AS operativo,
            (SELECT COUNT(*)  FROM app_productos p4 RIGHT JOIN stock_qrcode qr4 ON qr4.id_qrcode = p4.id_qr WHERE p4.id IS NOT NULL AND p4.estado_producto = '2' AND qr4.estado_qrcode = '1' AND p4.idproducto = p.idproducto) AS transito,
            (SELECT COUNT(*) FROM app_productos p5 RIGHT JOIN stock_qrcode qr5 ON qr5.id_qrcode = p5.id_qr WHERE p5.id IS NOT NULL AND p5.estado_producto = '3' AND qr5.estado_qrcode = '1' AND p5.idproducto = p.idproducto) AS reparacion,
            COUNT(p.idproducto) AS total,
            CURRENT_DATE as fecha
            FROM app_productos p
            RIGHT JOIN stock_qrcode qr ON qr.id_qrcode = p.id_qr
            LEFT JOIN productos pp ON pp.`id_producto` = p.idproducto
            WHERE p.id IS NOT NULL
            AND pp.producto as prod_nombre IS NOT NULL
            AND pp.`id_producto`_estado = '1'
            AND pp.tipoagrupacion = '1'
            AND qr.estado_qrcode = '1'
            GROUP BY p.idproducto;";
            $stmt  = $db->query($sql);
            $data  = $stmt->fetchAll(PDO::FETCH_OBJ);
            $array = json_decode(json_encode($data), true);

            $sql       = "INSERT INTO `inf_cantmaqxtipoyestado` (`producto as prod_nombre`, `prod_descrip`, `prod_foto`, `idproducto`, `operativo`, `disponible`, `transito`, `reparacion`, `desechar`, `total`, `fecha`) VALUES (:producto as prod_nombre, :prod_descrip, :prod_foto, :idproducto, :operativo, :disponible, :transito, :reparacion, :desechar, :total, :fecha)";
            $sentencia = $db->prepare($sql);
            foreach ($array as $fila) {
                $sentencia->execute($fila);
            }
            return array('code' => 1, 'status' => 'success', 'message' => '', 'data' => $data);
        } else {
            return array('code' => 1, 'status' => 'success', 'message' =>
                'Ya se cargo el informe de la fecha ' . $fecha, 'data' => '');
        }

    }

}
