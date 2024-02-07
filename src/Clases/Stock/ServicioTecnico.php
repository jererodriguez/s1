<?php
namespace Clases\Stock;

use Clases\Stock\Appcomponentes;
use \PDO;

class ServicioTecnico
{
    public static function aperturadepuerta(pdo $db, $form): array
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
                return array('code' => 1, 'status' => 'success', 'message' => 'Apertura de puerta registrada exitosamente id ' . $id .', prod id '.$form['idprod'].'.', 'data' => '');
            } else {
                $mensajeerror = 'No se pudo cambiar el estado del producto ID ' . $form['idprod'];
                return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');}
        } else {
            $mensajeerror = 'El id ' . $form['idprod'] . ' no existe en la base de datos';
            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');
        }
    }

    public static function sustituir(pdo $db, $idprod, $qr, $idprodconjunto, $idusu): array
    {
        $sql = "SELECT p.id, p.idproducto, p.iddeposito, dep.dep_nombre, pp.producto as prod_nombre from app_productos p left join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos pp on pp.`id_producto` = p.idproducto left join stock_depositos dep on dep.id_dep = p.iddeposito where qr.qr_code = '" . $qr . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        $iddep2 = $data->iddeposito ?? 0;
        $iddep2_nombre = $data->dep_nombre ?? 0;
        $idprodnew = $data->id ?? 0;
        $idproductonew = $data->idproducto ?? 0;
        $prodnombrenew = $data->prod_nombre ?? 0;
        $sql = "SELECT p.id, p.idproducto, pp.producto as prod_nombre, qr.qr_code from app_productos p left join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos pp on pp.`id_producto` = p.idproducto where p.id = '" . $idprod . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_OBJ);
        $qrreemplazado = $info->qr_code ?? 0;
        $idproducto = $info->idproducto ?? 0;
        $prodnombre = $info->prod_nombre ?? 0;
        if ($idproducto !== $idproductonew) {
            $mensajeerror = 'Esta procurando asignar un componente ' . $prodnombrenew . ' en un componente ' . $prodnombre . ' verifique que esta reemplazando la pieza correcta.';
            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');
        }

        $sql = "SELECT p.id, p.idproducto, pp.producto as prod_nombre, qr.qr_code from app_productos p left join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos pp on pp.`id_producto` = p.idproducto where p.id = '" . $idprod . "' and p.iddeposito = '" . $iddep2 . "'";
        if ($stmt->rowCount() == 0) {
            $sql = "SELECT * from app_productos p left join stock_depositos dep on dep.id_dep = p.iddeposito where p.id = '" . $idprod . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $depnombre = $data->dep_nombre ?? 0;
            return array('code' => -1, 'status' => 'fail', 'message' => 'Error: El nuevo componente tiene que estar en el mismo deposito realice la remision del repuesto al deposito ' . $depnombre, 'data' => '');
        }

        $sql = "SELECT *  FROM `app_componentes` compo left join app_productos p on p.id = compo.idprodconjunto_compoapp left join productos pp on pp.`id_producto` = p.idproducto left join stock_qrcode qr on qr.id_qrcode = p.id_qr WHERE `idprod_compoapp` = '" . $idprodnew . "' ORDER BY `id_compoapp`  DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $conj_nombre = $data->prod_nombre ?? 0;
            $conj_idmaq = $data->idmaq ?? 0;
            $conj_qr = $data->qr_code ?? 0;
            return array('code' => -1, 'status' => 'fail', 'message' => 'El componente esta asignado a la maquina' . $conj_nombre . ' QR ' . $conj_qr . ' Id maq ' . $conj_idmaq . ' procure primero desagrupar el componente antes de asignarlo a otra maquina.', 'data' => '');

        } 
        
        $sql = "SELECT * FROM `app_componentes` WHERE `idprod_compoapp` = '" . $idprod . "' and idprodconjunto_compoapp = '" . $idprodconjunto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data_info = $stmt->fetch(PDO::FETCH_OBJ);
        $idcompo = $data_info->idcompo_compoapp ?? 0;

        $del = "DELETE FROM `app_componentes` WHERE `app_componentes`.`idprod_compoapp` = '" . $idprod . "'";
        $stmt = $db->prepare($del);
        $stmt->execute();

        $insertar = "INSERT INTO `app_componentes` (`id_compoapp`, `idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES (NULL, '" . $idcompo . "', '" . $idprodnew . "', '" . $idprodconjunto . "')";

        $stmt = $db->prepare($insertar);
        $stmt->execute();

        ServicioTecnico::cambiarEstado($db, $idprodnew, $idusu, '1', 'Componente QR ' . $qr . ' reemplazando al QR' . $qrreemplazado);

        $sql = "SELECT * FROM `app_componentes` WHERE `idprod_compoapp` = '" . $idprodnew . "' and idprodconjunto_compoapp = '" . $idprodconjunto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return array('code' => 1, 'status' => 'success', 'message' => 'El reeemplazo se realizo exitosamente', 'data' => '');
        } else {
            return array('code' => -1, 'status' => 'fail', 'message' => 'No se realizo el reemplazo', 'data' => '');
        }

    }
    public static function getRegistros(pdo $db, $qrcode, $idusu): array
    {

        $sql = "SELECT p.id, id_dep, qr_code, producto as prod_nombre, u.id_usu, pp.descripcion as prod_descrip, 'pic.jpg' as prod_foto, dep_ciudad, dep_nombre, dep_telefono, nombre_usu, apellido_usu, telefono_usu, estado_producto, p.descripcion, p.idmaq, CASE
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

        if ($idusuario != $idusu) {
            $sql = "SELECT nombre_usu, apellido_usu FROM `stock_usuarios` where id_usu = '" . $idusu . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $nombreusu = $data->nombre_usu . ' ' . $data->apellido_usu;

            $mensajeerror = 'El QR ' . $infoqr . ' ' . $infoprodnombre . ' asignado al deposito ' . $depnombre . ' - ' . $usunombre . ' no corresponde al usuario ' . $nombreusu . '. Realice la remision del QR a su deposito para poder continuar. ';
            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');
        }
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
            $info = array('info' => $data);
            $sql = "SELECT
            id_mlog,
            CASE
              WHEN op_mlog = '1'
              THEN 'recepcion'
              WHEN op_mlog = '-1'
              THEN 'remision'
              ELSE ''
            END AS operacion,
            pp.producto as prod_nombre,
            p.idmaq,
            qr.`qr_code`,
            dep1.`dep_nombre` AS dep1_nombre,
            dep1.`dep_ciudad` AS dep1_ciudad,
            uremitente.`id_usu` AS dep1_idusu,
            CONCAT (
              uremitente.`nombre_usu`,
              ' ',
              uremitente.`apellido_usu`
            ) AS dep1_agente,
            i.movi_id,
            dep2.`dep_nombre` AS dep2_nombre,
            dep2.`dep_ciudad` AS dep2_ciudad,
            ureceptor.`id_usu` AS dep2_idusu,
            CONCAT (
              ureceptor.`nombre_usu`,
              ' ',
              ureceptor.`apellido_usu`
            ) AS dep2_agente,
            mlog.`fechahora_mlog`,
            op_mlog, iddep1_mlog, iddep2_mlog
             FROM log_movstock mlog
            LEFT JOIN stock_mov_items i ON i.`movi_id` = mlog.`idimov_mlog`
            LEFT JOIN stock_mov m ON i.`movi_idmov` = m.mov_id
            LEFT JOIN stock_depositos dep1 ON dep1.`id_dep` = mlog.`iddep1_mlog`
            LEFT JOIN stock_depositos dep2 ON dep2.`id_dep` = mlog.`iddep2_mlog`
            LEFT JOIN stock_usuarios uremitente ON uremitente.`id_usu` = m.`mov_idusu`
            LEFT JOIN stock_usuarios ureceptor ON ureceptor.`id_usu` = mlog.`idusu_mlog`
            LEFT JOIN stock_qrcode qr ON qr.`qr_code` = mlog.`qr_mlog`
            LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
            LEFT JOIN productos pp ON pp.`id_producto` = p.`idproducto` WHERE qr_mlog = '" . $qrcode . "'  ORDER BY `mlog`.`fechahora_mlog` ASC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $info['mlog'] = $data;

            //Inicio select conjunto
            $sql = "SELECT
            pp.`id_producto`,
            appcompo.idcompo_compoapp AS idcompo,
            p.idmaq,
            pp.producto AS prod_nombre,
            qr.qr_code,
            pp.tipoagrupacion
        FROM
            `app_componentes` appcompo
        LEFT JOIN stock_productos_compo compo ON
            compo.id_compo = appcompo.idcompo_compoapp
        LEFT JOIN stock_productos_cat cat ON
            cat.id_cat = compo.idcat_compo
        LEFT JOIN stock_productos_conjunto conjunto ON
            conjunto.id_conjunto = cat.idconjunto_cat
        LEFT JOIN app_productos p ON
            p.`id` = appcompo.idprodconjunto_compoapp
        LEFT JOIN productos pp ON
            p.idproducto = pp.`id_producto`
        LEFT JOIN stock_qrcode qr ON
            qr.id_qrcode = p.id_qr
            where appcompo.idprod_compoapp = '" . $idprod . "';";
            $stmt = $db->query($sql); 
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dataurl = Appcomponentes::getcatbyqr($db, $qrcode);
            $url = '?idproducto=' . $dataurl->idproducto . '&idconjunto=' . $dataurl->idconjunto . '&idcategoria=' . $dataurl->idcat . '&idpadrecat=' . $dataurl->idpadrecat . '&iddeposito=' . $dataurl->iddeposito . '&appprodid=' . $dataurl->appprodid;

            $info['conjunto'] = $data;
            $info['conjunto']['url'] = $url;

//Fin select conjunto

           /* $sql = "SELECT
            log.log_id, log.log_idprod, pp1.producto as prod_nombre, log.log_estado,    CASE
                    WHEN log.log_estado = '-1' THEN 'Desechar'
                    WHEN log.log_estado = '0' THEN 'Disponible'
                    WHEN log.log_estado = '1' THEN 'Operativo'
                    WHEN log.log_estado = '2' THEN 'Transito'
                    WHEN log.log_estado = '3' THEN 'Repararacion'
                END AS prod_estado, log_fechahora, log_idusu, u.nombre_usu, u.apellido_usu, u.telefono_usu,log.log_iddep, dep.dep_nombre, log.log_idprodconjunto, pp2.producto as prod_nombre as conjunto, p2.idmaq, log.log_comentario
            FROM `log_estado` log left join app_productos p1 on p1.id = log.log_idprod left join productos pp1 ON pp1.id = p1.idproducto left join stock_depositos dep on dep.id_dep = log_iddep
            left join stock_usuarios u on u.id_usu = log.log_idusu left join app_productos p2 on p2.id = log.log_idprodconjunto left join productos pp2 on pp2.id = p2.idproducto where log.log_idprod = '" . $idprod . "'
            ORDER BY `log`.`log_fechahora` DESC";*/
            $sql = "SELECT
            log.log_id, log.log_idprod, pp1.producto as prod_nombre, log.log_estado,    CASE
                    WHEN log.log_estado = '-1' THEN 'Desechar'
                    WHEN log.log_estado = '0' THEN 'Disponible'
                    WHEN log.log_estado = '1' THEN 'Operativo'
                    WHEN log.log_estado = '2' THEN 'Transito'
                    WHEN log.log_estado = '3' THEN 'Reparacion'
                END AS prod_estado, log_fechahora, log_idusu, u.nombre_usu, u.apellido_usu, u.telefono_usu,log.log_iddep, dep.dep_nombre, log.log_idprodconjunto, pp2.producto as conjunto, p2.idmaq, log.log_comentario
            FROM `log_estado` log left join app_productos p1 on p1.id = log.log_idprod left join productos pp1 ON pp1.id_producto = p1.idproducto left join stock_depositos dep on dep.id_dep = log_iddep
            left join stock_usuarios u on u.id_usu = log.log_idusu left join app_productos p2 on p2.id = log.log_idprodconjunto left join productos pp2 on pp2.id_producto = p2.idproducto where log.log_idprod = '" . $idprod . "'
            ORDER BY `log`.`log_fechahora` DESC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $info['log_estado'] = $data;
            return array('code' => 1, 'status' => 'success', 'message' => $mensajeok, 'data' => $info);
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

    public static function cambiarEstado(pdo $db, $idprod, $idusu, $estado, $comentario)
    {

        $sql = "SELECT p.id, p.iddeposito as iddep, compo.idprodconjunto_compoapp as conjunto FROM app_productos p left join app_componentes compo on compo.idprod_compoapp = p.id WHERE p.id = '" . $idprod . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $fecha = date('Y-m-d H:i:s');
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $idconjunto = $data->conjunto ?? 0;
            $insertar = "INSERT INTO `log_estado` (`log_id`, `log_idprod`, `log_estado`, `log_fechahora`, `log_idusu`, `log_iddep`, `log_idprodconjunto`, `log_comentario`) VALUES (NULL, '" . $idprod . "', '" . $estado . "', '" . $fecha . "', '" . $idusu . "', '" . $data->iddep . "', '" . $idconjunto . "', '" . $comentario . "');";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $sql = "update `app_productos` set `estado_producto` = '" . $estado . "' where `id` = '" . $idprod . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return true;
        } else {
            return false;
        }
    }

    public static function desagrupar(pdo $db, $idprod)
    {

        $sql = "SELECT p.id, p.iddeposito as iddep, compo.idprodconjunto_compoapp as conjunto FROM app_productos p left join app_componentes compo on compo.idprod_compoapp = p.id WHERE p.id = '" . $idprod . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $fecha = date('Y-m-d H:i:s');
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $del = "DELETE FROM `app_componentes` WHERE `app_componentes`.`idprod_compoapp` = '" . $idprod . "'";
            $stmt = $db->prepare($del);
            $stmt->execute();
            return true;
        } else {
            return false;
        }
    }

}
