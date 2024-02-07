<?php
namespace Clases\Stock;
use \PDO;
class Verdeposito
{
    public static function verdeposito(pdo $db, $idusu)
    {
        $sql = "SELECT dep_nombre, producto as prod_nombre, qr_code, p.`estado_producto`, CASE
        WHEN estado_producto = '-1' THEN 'Desactivado' 
        WHEN estado_producto = '0' THEN 'Disponible' 
        WHEN estado_producto = '1' THEN 'Operativo' 
        WHEN estado_producto = '2' THEN 'En transito' 
        WHEN estado_producto = '3' THEN 'En reparacion' 
        ELSE '--' END AS estado FROM stock_depositos dep
        RIGHT JOIN app_productos p ON p.`iddeposito` = dep.`id_dep`
        LEFT JOIN productos pp ON pp.`id_producto` = p.`idproducto`
        LEFT JOIN stock_qrcode qr ON qr.`id_qrcode` = p.id_qr
        WHERE dep.`dep_idusu` = '".$idusu."'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function verdeposito_estado(pdo $db, $qr, $idusu, $newestado)
    {
        $sql = "SELECT * FROM app_productos  p RIGHT JOIN stock_depositos dep ON p.`iddeposito` = dep.`id_dep` RIGHT JOIN stock_qrcode qr ON qr.`id_qrcode` = p.id_qr WHERE qr.`qr_code` = '".$qr."' AND dep.`dep_idusu` = '".$idusu."'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $fecha = date('Y-m-d H:i:s');
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $insertar = "insert into `log_estado` (`qr_est`, `val_est`, `idusu_est`, `iddep_est`, `fechahora_est`) values ('".$qr."', '".$newestado."', '".$idusu."', '".$data->iddeposito."', '".$fecha."');";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $sql = "update `app_productos` set `estado_producto` = '".$newestado."' where `id` = '".$data->id."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return true;
        } else {
            return false;
        }
    }


}