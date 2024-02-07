<?php
namespace Clases\Stock;

use \PDO;

class Lectorqr
{
    public static function lectorqr1(pdo $db, $qrcode, $idusu): array
    {
        $sql = "SELECT qr_code, producto as prod_nombre, pp.prod_descrip, nombrefoto, dep_ciudad, dep_nombre, dep_telefono, nombre_usu, apellido_usu, telefono_usu, estado_producto, p.descripcion, CASE
        WHEN estado_producto = '-1' THEN 'Desactivada'
        WHEN estado_producto = '0' THEN 'Disponible'
        WHEN estado_producto = '1' THEN 'Operativa'
        WHEN estado_producto = '2' THEN 'En transito'
        WHEN estado_producto = '3' THEN 'En reparacion'
        ELSE '-'
    END as estado_prod,
    app_fabricacion, app_fechavencimiento, app_lote, app_cantidad_saldo, unidad_medida, CASE
        WHEN unidad_medida = '1' THEN 'Unidades'
        WHEN unidad_medida = '2' THEN 'Mililitros'
        WHEN unidad_medida = '3' THEN 'Gramos'
    END AS medida, pp.tipoagrupacion,         CASE
                    WHEN pp.tipoagrupacion = '0' THEN 'Componentes'
                    WHEN pp.tipoagrupacion = '1' THEN 'Maquinas'
                    WHEN pp.tipoagrupacion = '2' THEN 'Granel'
                END AS agrupacion, estado_qrcode, descripcion_serie, id_serie
     FROM stock_qrcode qr
        left JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
        left JOIN productos pp ON pp.`id_producto` = p.`idproducto`
        left JOIN stock_depositos dep ON dep.`id_dep` = p.`iddeposito`
        left JOIN stock_usuarios u ON u.`id_usu` = dep.`dep_idusu`
        left join stock_qrcode_serie s on s.id_serie = qr.idserie
        WHERE qr.`qr_code` = '" . $qrcode . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data->estado_qrcode == '1') {
            $mensajeok = "Producto tipo: " . $data->agrupacion . "\r\n";
            $mensajeok .= "Producto: " . $data->prod_nombre . "\r\n";
            $mensajeok .= "Fecha de fabricacion: " . $data->app_fabricacion . "\r\n";
            if ($data->tipoagrupacion == '2') {
                $mensajeok .= "Cantidad: " . $data->app_cantidad_saldo . " " . $data->medida . "\r\n";
            }
            $mensajeok .= "Lote: " . $data->app_lote . "\r\n";
            $mensajeok .= "Fecha de Vencimiento: " . $data->app_fechavencimiento . "\r\n";
            if ($data->dep_ciudad != '') {
                $mensajeok .= "Ciudad: " . $data->dep_ciudad . "\r\n";
            }
            $mensajeok .= "Deposito: " . $data->dep_nombre . "\r\n";
            if ($data->dep_telefono != '') {
                $mensajeok .= "Tel. Deposito: " . $data->dep_telefono . "\r\n";
            }
            $mensajeok .= "Agente: " . $data->nombre_usu . " " . $data->apellido_usu . "\r\n";
            if ($data->telefono_usu != '') {
                $mensajeok .= "Tel. Agente: " . $data->telefono_usu . "\r\n";
            }
            if ($data->tipoagrupacion == '0') {
                $mensajeok .= "Estado del producto: " . $data->estado_prod . "\r\n";
                $mensajeok .= "Descripcion: " . $data->prod_descrip . "\r\n";
            }
            $mensajeok .= "QR: " . $data->qr_code;
            return array('code' => 1, 'status' => 'success', 'message' => $mensajeok, 'data' => '');
        } else if ($data->estado_qrcode == '0') {
            return array('code' => 0, 'status' => 'success', 'message' => 'El QR esta disponible. Serie QR: ' . $data->descripcion_serie . ' (#' . $data->id_serie . ')', 'data' => '');
        } else if ($data->estado_qrcode == '-1') {

            $mensajeok = 'El QR esta desactivado. Serie QR: ' . $data->descripcion_serie . ' (#' . $data->id_serie . ')' . "\r\n";

            $mensajeok .= "Producto tipo: " . $data->agrupacion . "\r\n";
            $mensajeok .= "Producto: " . $data->prod_nombre . "\r\n";
            $mensajeok .= "Fecha de fabricacion: " . $data->app_fabricacion . "\r\n";
            if ($data->tipoagrupacion == '2') {
                $mensajeok .= "Cantidad: " . $data->app_cantidad_saldo . " " . $data->medida . "\r\n";
            }
            $mensajeok .= "Lote: " . $data->app_lote . "\r\n";
            $mensajeok .= "Fecha de Vencimiento: " . $data->app_fechavencimiento . "\r\n";
            if ($data->dep_ciudad != '') {
                $mensajeok .= "Ciudad: " . $data->dep_ciudad . "\r\n";
            }
            $mensajeok .= "Deposito: " . $data->dep_nombre . "\r\n";
            if ($data->dep_telefono != '') {
                $mensajeok .= "Tel. Deposito: " . $data->dep_telefono . "\r\n";
            }
            $mensajeok .= "Agente: " . $data->nombre_usu . " " . $data->apellido_usu . "\r\n";
            if ($data->telefono_usu != '') {
                $mensajeok .= "Tel. Agente: " . $data->telefono_usu . "\r\n";
            }
            if ($data->tipoagrupacion == '0') {
                $mensajeok .= "Estado del producto: " . $data->estado_prod . "\r\n";
                $mensajeok .= "Descripcion: " . $data->prod_descrip . "\r\n";
            }
            $mensajeok .= "QR: " . $data->qr_code;

            return array('code' => -1, 'status' => 'success', 'message' => $mensajeok, 'data' => '');

        }
        if (!$data) {
            return array('code' => 404, 'status' => 'success', 'message' => 'El Cod. QR no existe', 'data' => '');
        }
    }
}
