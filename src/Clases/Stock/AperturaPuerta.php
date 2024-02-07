<?php
namespace Clases\Stock;

use Clases\Stock\ServicioTecnico;
use \PDO;

class AperturaPuerta
{

    public static function cargarfoto($db, $foto = [])
    {


        $sql = "UPDATE `log_aper_puerta` SET `ap_nombrefoto` = '".$foto['nombrefoto']."' WHERE `log_aper_puerta`.`id_ap` = '".$foto['id']."';";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        
    }

    public static function getRegistros(pdo $db, $qrcode, $idusu): array
    {

        $sql = "SELECT p.id, id_dep, qr_code, producto as prod_nombre, u.id_usu, pp.prod_descrip, pp.prod_foto, dep_ciudad, dep_nombre, dep_telefono, nombre_usu, apellido_usu, telefono_usu, estado_producto, p.descripcion, p.idmaq, CASE
        WHEN estado_producto = '-1' THEN 'Desactivada'
        WHEN estado_producto = '0' THEN 'Disponible'
        WHEN estado_producto = '1' THEN 'Operativo'
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
        $idprod = $data->id;
        $iddep = $data->id_dep;
        $depnombre = $data->dep_nombre;
        $usunombre = $data->nombre_usu . ' ' . $data->apellido_usu;
        $idusuario = $data->id_usu;
        $infoqr = $data->qr_code;
        $infoprodnombre = $data->prod_nombre;

        if ($stmt->rowCount() == 0) {
            return array('code' => -1, 'status' => 'fail', 'message' => 'No se encontro ningun producto activo con el QR ' . $qrcode, 'data' => '');

        }

        if ($data->estado_qrcode == '1' and $data->tipoagrupacion == '1') {
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
            $info = array('info' => $data);
            $sql = "SELECT
            ap.id_ap,
            ap.ap_idprod,
            ap.ap_nombrefoto,
            p.estado_producto,
            p.idmaq,
            pp.producto as prod_nombre,
            ap.ap_cont_entrada,
            ap.ap_cont_salida,
            ap.ap_fisico,
            ap.ap_fechahora,
            ap.ap_idmotivo,
            CASE
                    WHEN ap.ap_idmotivo = '1' THEN 'Recaudacion'
                    WHEN ap.ap_idmotivo = '2' THEN 'Otro'
                END AS motivo,
            ap.ap_otro,
            ap.ap_gps_lat,
            ap.ap_gps_lon,
            ap.ap_gps_precision,
            qr.qr_code,
            pp.producto as prod_nombre,
            pp.tipoagrupacion,
            pp.prod_descrip,
            pp.prod_foto,
            ap.ap_iddep,
            dep.dep_nombre,
            dep.dep_ciudad,
            ap.ap_idusu,
            u.nombre_usu,
            u.apellido_usu,
            u.telefono_usu
            
            
            FROM `log_aper_puerta` ap 
            left join app_productos p on p.id = ap.ap_idprod
            left join stock_depositos dep on dep.id_dep = ap.ap_iddep
            left join stock_usuarios u on u.id_usu = ap_idusu
            left join stock_qrcode qr on qr.id_qrcode = p.id_qr
            left join productos pp on pp.`id_producto` = p.idproducto where qr.qr_code like '".$qrcode."' and pp.tipoagrupacion = '1'  
            ORDER BY `ap`.`ap_fechahora`  DESC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $info['ap'] = $data;
            return array('code' => 1, 'status' => 'success', 'message' => $mensajeok, 'data' => $info);



        }
        
        else if ($data->tipoagrupacion != '1') {
            $mensajeok = 'El QR no es un producto tipo maquina. Serie QR: ' . $data->descripcion_serie . ' (#' . $data->id_serie . ')' . "\r\n";

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

        else if ($data->estado_qrcode == '0') {
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

    public static function insert(pdo $db, $form): array
    {
        $sql = "SELECT p.id, p.iddeposito as iddep, compo.idprodconjunto_compoapp as conjunto FROM app_productos p left join app_componentes compo on compo.idprod_compoapp = p.id WHERE p.id = '" . $form['idprod'] . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $iddep = $data->iddep ?? 0;
            $insertar = "INSERT INTO `log_aper_puerta` (`id_ap`, `ap_idprod`,`ap_iddep`, `ap_idusu`, `ap_cont_entrada`, `ap_cont_salida`, `ap_fisico`, `ap_idmotivo`, `ap_gps_lat`, `ap_gps_lon`, `ap_gps_precision`, `ap_gps_hora`, `ap_fechahora`) VALUES (NULL, '" . $form['idprod'] . "', '" . $iddep . "', '" . $form['idusu'] . "', '" . $form['entrada'] . "', '" . $form['salida'] . "', '" . $form['fisico'] . "', '" . $form['motivo'] . "', '" . $form['lat'] . "', '" . $form['lon'] . "', '" . $form['precision'] . "', '" . $form['gpshora'] . "', '" . $form['fecha'] . "')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $id = $db->lastInsertId();

            if (ServicioTecnico::cambiarEstado($db, $form['idprod'], $form['idusu'], '1', 'Apertura de puerta')) {
                return array('code' => 1, 'status' => 'success', 'message' => 'Apertura de puerta registrada exitosamente id ' . $id .', prod id '.$form['idprod'].'.', 'id' => $id);
            } else {
                $mensajeerror = 'No se pudo cambiar el estado del producto ID ' . $form['idprod'];
                return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');}
        } else {
            $mensajeerror = 'El id ' . $form['idprod'] . ' no existe en la base de datos';
            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');
        }
    }

}
