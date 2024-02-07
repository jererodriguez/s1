<?php

namespace Clases\Stock;

use \PDO;


class Agrupar
{

    public static function agrupar_1(pdo $db, $idusu, $tipoagrupacion, $idproducto, $idpersonalizado, $iddeposito, $idqr, $descripcion, $lat, $lon, $precision, $ubicacion_hora): int
    {
        /*
            Insertar en app_productos 25/03/2022
            Hecho 28/03/2022
        */
        $fecha = date('Y-m-d h:i:s');
        $insertar = "INSERT INTO " . DB_BASE . ".`stock_ubi_gps` (`id_prod`, `lat_gps`, `lon_gps`, `precision_gps`, `hora_gps`, `hora_sistema`) VALUES ('0', '$lat', '$lon', '$precision', '$ubicacion_hora', '$fecha')";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $idgps = $db->lastInsertId();

        $sql = "INSERT INTO " . DB_BASE . ".app_productos (id_qr, idproducto, tipoagrupacion, idmaq, idgps, idfoto, iddeposito, descripcion, estado_producto) VALUES ('$idqr', '$idproducto', '$tipoagrupacion', '$idpersonalizado', '$idgps', '0', '$iddeposito', '$descripcion', '0')";
        $stmt = $db->query($sql);
        $id = $db->lastInsertId();

        $sql = "UPDATE " . DB_BASE . ".`stock_qrcode` SET `estado_qrcode` = '1', idusu = '$idusu', fecha_activacion = '$fecha', idgps = '$idgps' WHERE `id_qrcode` = '$idqr';";
        $stmt = $db->query($sql);
        return $id;
    }

    public static function tipoMaquina(pdo $db): array
    {
        $sql = "SELECT id, producto as prod_nombre, prod_foto, prod_descrip FROM productos catalogo WHERE id_estado_producto = '1' AND tipoagrupacion = '1'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function getComponentes(pdo $db, $id): array
    {

        $sql = "SELECT
        agrupar.id AS idgrupo,
        agrupar.descripcion AS nombregrupo,
        agrupar.idmaq AS nombreproducto,
        pdetalles.nombrefoto AS fotoproducto,
        agrupar.`descripcion` AS descripciongrupo,
        componentes.id AS idcomponente,
        pdetalles.`id` AS idproducto,
        pdetalles.`descripcion` AS descripcionproducto,
        pdetalles.`nombrefoto` AS fotoproducto,
        pdetalles.id_qr AS idqr,
        qrcode.qr_code,
        pcabecera.producto as prod_nombre AS nombrecomponente 
        
        FROM " . DB_BASE . ".app_productos AS agrupar
        LEFT JOIN " . DB_BASE . ".app_agrupar_componentes AS componentes ON (componentes.idagrupar = agrupar.id)
        LEFT JOIN " . DB_BASE . ".app_productos pdetalles ON ( pdetalles.id = componentes.idproducto)
        LEFT JOIN " . DB_BASE . ".productos pcabecera ON (pcabecera.id = pdetalles.idproducto)
        LEFT JOIN " . DB_BASE . ".stock_qrcode qrcode ON (qrcode.`id_qrcode` = pdetalles.`id_qr`)
        
        WHERE agrupar.id = '$id'";
        //var_dump($sql);
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }



    public static function agrupar_2(pdo $db, $qr, $idAgrupar)
    {
        $sql = "SELECT p.id from " . DB_BASE . ".app_productos p LEFT JOIN stock_qrcode qr ON p.`id_qr` = qr.`id_qrcode`  WHERE qr.qr_code = '" . $qr . "'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idProducto = $data[0]->id;
        //var_dump($sql);

        $sql = "SELECT id from " . DB_BASE . ".app_agrupar_componentes WHERE idproducto = '" . $idProducto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 0;
        }

        $sql = "INSERT INTO " . DB_BASE . ".app_agrupar_componentes (idproducto, idagrupar) VALUES ($idProducto,$idAgrupar)";

        $stmt = $db->query($sql);
        $id = $db->lastInsertId();

        $sql = "update " . DB_BASE . ".`app_productos` set `estado_producto` = '1' where `id` = '$idProducto';";

        $stmt = $db->query($sql);

        return $id;
    }

    public static function eliminarComponente(pdo $db, $idComponente): int
    {


        $sql = "SELECT idproducto from " . DB_BASE . ".app_agrupar_componentes WHERE id = '" . $idComponente . "'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idProducto = $data[0]->idproducto;

        $sql = "update " . DB_BASE . ".`app_productos` set `estado_producto` = '0' where `id` = '$idProducto';";
        $stmt = $db->query($sql);

        $sql = "DELETE FROM " . DB_BASE . ".`app_agrupar_componentes` WHERE `id` = '" . $idComponente . "'";
        $stmt = $db->query($sql);

        return true;
    }

    public static function desagrupar(pdo $db, $qr)
    {
        $sql = "SELECT p.id from " . DB_BASE . ".app_productos p LEFT JOIN stock_qrcode qr ON p.`id_qr` = qr.`id_qrcode`  WHERE qr.qr_code = '" . $qr . "'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idComponente = $data[0]->id;

        $sql = "update " . DB_BASE . ".`app_productos` set `estado_producto` = '0' where `id` = '$idComponente';";
        $stmt = $db->query($sql);

        $sql = "DELETE FROM " . DB_BASE . ".`app_agrupar_componentes` WHERE `idproducto` = '" . $idComponente . "'";
        $stmt = $db->query($sql);
        return true;
    }
}
