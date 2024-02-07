<?php

namespace Clases\Stock;

use \PDO;

class Movimiento
{

    public static function recibirmov(pdo $db, $mov_id, $idusu)
    {

        /* INICIO LOG MOVIMIENTO DE MAQUINAS */
      
        $sql = "SELECT
        *
      FROM
        stock_mov m
        LEFT JOIN stock_mov_items i
          ON i.`movi_idmov` = m.`mov_id`
        LEFT JOIN stock_qrcode qr ON qr.`qr_code` = movi_itemqr
        LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
      WHERE mov_id = '" . $mov_id . "' and movi_itemrecibido = '0' ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datalog = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($datalog as $fila) {

            $reg = array();
            $reg['idimov'] = $fila->movi_id;
            $reg['iddep1'] = $fila->iddeposito;
            $reg['iddep2'] = $fila->mov_iddep;
            $reg['idusu'] = $idusu;
            $reg['qr'] = $fila->qr_code;
            $reg['lat'] = "";
            $reg['lon'] = "";
            $reg['precision'] = "";
            $reg['operacion'] = "1";
       
            $idlog = Movimiento::mlog($db, $reg);
            /* FIN LOG MOVIMIENTO DE MAQUINAS */

            $sql = "update
            `stock_mov_items`
          set
            `movi_itemrecibido` = '1',
            `movi_aceptado` = '1'
          where `movi_idmov` = '" . $mov_id . "';
          ";
            $stmt = $db->query($sql);

            $sql = "update
            `app_productos`
          set
            `iddeposito` = '" . $fila->mov_iddep . "'
          where `id_qr` = '" . $fila->id_qr . "'";
            $stmt = $db->query($sql);

        }

        return true;
    }

    public static function movRecepcionar(pdo $db, $idusu, $qr)
    {
        $sql = "SELECT
        *
        FROM
          stock_mov m
          LEFT JOIN stock_mov_items i ON i.`movi_idmov` = m.`mov_id`
          LEFT JOIN stock_qrcode qr ON qr.`qr_code` = movi_itemqr
          LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
          LEFT JOIN productos pp ON pp.`id_producto` = p.`idproducto`
          LEFT JOIN stock_depositos dep ON dep.`id_dep` = m.`mov_iddep`
          LEFT JOIN stock_usuarios u ON u.`id_usu` = dep.`dep_idusu`
        WHERE i.`movi_itemrecibido` = '0' AND dep.`dep_idusu` = '" . $idusu . "' AND movi_itemqr = '" . $qr . "'
        ORDER BY mov_fechaini DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return false;
        }
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $itemId = $data[0]->movi_id;
        $mov_id = $data[0]->mov_id;

        /* INICIO LOG MOVIMIENTO DE MAQUINAS */
        $sql = "SELECT * FROM stock_mov m LEFT JOIN stock_mov_items i ON i.`movi_idmov` = m.`mov_id` WHERE mov_id = '" . $mov_id . "' AND movi_itemqr = '" . $qr . "'";

        $sql = "SELECT
        *
      FROM
        stock_mov m
        LEFT JOIN stock_mov_items i
          ON i.`movi_idmov` = m.`mov_id`
        LEFT JOIN stock_qrcode qr ON qr.`qr_code` = movi_itemqr
        LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
      WHERE mov_id = '" . $mov_id . "'
        AND movi_itemqr = '" . $qr . "'";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datalog = $stmt->fetchAll(PDO::FETCH_OBJ);
        $iddep2 = $datalog[0]->mov_iddep;

        $reg = array();
        $reg['idimov'] = $itemId;
        $reg['iddep1'] = $datalog[0]->iddeposito;
        $reg['iddep2'] = $datalog[0]->mov_iddep;
        $reg['idusu'] = $idusu;
        $reg['qr'] = $qr;
        $reg['lat'] = "";
        $reg['lon'] = "";
        $reg['precision'] = "";
        $reg['operacion'] = "1";
        $idlog = Movimiento::mlog($db, $reg);
        /* FIN LOG MOVIMIENTO DE MAQUINAS */

        $sql = "update
        `stock_mov_items`
      set
        `movi_itemobs` = '',
        `movi_itemrecibido` = '1',
        `movi_aceptado` = '1'
      where `movi_id` = '" . $itemId . "';
      ";
        $stmt = $db->query($sql);

        $sql = "update
        `app_productos`
      set
        `iddeposito` = '" . $data[0]->mov_iddep . "'
      where `id_qr` = '" . $data[0]->id_qr . "'";
        $stmt = $db->query($sql);

        return $data;
    }

    public static function mlog(pdo $db, $reg)
    {
        $fechahora = date('Y-m-d H:i:s');

        $insertar = "insert into `log_movstock` (
            `idimov_mlog`,
            `iddep1_mlog`,
            `iddep2_mlog`,
            `idusu_mlog`,
            `qr_mlog`,
            `fechahora_mlog`,
            `lat_mlog`,
            `lon_mlog`,
            `precision_mlog`,
            `op_mlog`
          )
          values
            (
              '" . $reg['idimov'] . "',
              '" . $reg['iddep1'] . "',
              '" . $reg['iddep2'] . "',
              '" . $reg['idusu'] . "',
              '" . $reg['qr'] . "',
              '" . $fechahora . "',
              '" . $reg['lat'] . "',
              '" . $reg['lon'] . "',
              '" . $reg['precision'] . "',
              '" . $reg['operacion'] . "'
            );
          ";
       
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id = $db->lastInsertId();
        return $id;
    }

    public static function getTraslados(pdo $db, $idusu): array
    {
        $sql = "SELECT
        mov_id,
        dep_nombre,
        nombre_usu,
        apellido_usu,
        mov_fechaini,
        dep_ciudad,
        mov_obs,
        COUNT(movi_id) AS citems
        FROM
          stock_mov m
          LEFT JOIN stock_mov_items i ON i.`movi_idmov` = m.`mov_id`
          LEFT JOIN stock_depositos dep ON dep.`id_dep` = m.`mov_iddep`
          LEFT JOIN stock_usuarios u ON u.`id_usu` = dep.`dep_idusu`
          left join stock_qrcode qr on qr.qr_code = i.movi_itemqr 
        WHERE i.`movi_itemrecibido` = '0' AND m.`mov_idusu` = '" . $idusu . "'
        and qr.id_qrcode is not null
        GROUP BY mov_id
        ORDER BY mov_fechaini DESC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function getRecepciones(pdo $db, $idusu): array
    {

        $sql = "SELECT
        mov_id,
        dep_nombre,
        nombre_usu,
        apellido_usu,
        mov_fechaini,
        dep_ciudad,
        mov_obs,
        COUNT(movi_id) AS citems
        FROM
          stock_mov m
          LEFT JOIN stock_mov_items i ON i.`movi_idmov` = m.`mov_id`
          LEFT JOIN stock_depositos dep ON dep.`id_dep` = m.`mov_iddep`
          LEFT JOIN stock_usuarios u ON u.`id_usu` = dep.`dep_idusu`
        WHERE i.`movi_itemrecibido` = '0' AND dep.`dep_idusu` = '" . $idusu . "'
        GROUP BY mov_id
        ORDER BY mov_fechaini DESC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function insertmov(pdo $db, $iddep, $idusu, $obs)
    {
        $fecha = date('Y-m-d H:i:s');
        $insertar = "INSERT INTO `stock_mov` (`mov_fechaini`,`mov_iddep`,`mov_idusu`,`mov_obs`) VALUES ('" . $fecha . "', '" . $iddep . "', '" . $idusu . "', '" . $obs . "');";

        try {
            //var_dump($insertar);
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $id = $db->lastInsertId();

            return $id;

        } catch (\Throwable$th) {

            throw $th;
            print_r($th);
        }

    }

    public static function getItemmov(pdo $db, $id): array
    {

        $sql = "SELECT
  *
      FROM
        stock_mov m
        LEFT JOIN stock_mov_items i
          ON m.`mov_id` = i.`movi_idmov`
        LEFT JOIN stock_depositos d
          ON d.`id_dep` = m.`mov_iddep`
          LEFT JOIN stock_qrcode qr ON qr.`qr_code` = i.`movi_itemqr`
          LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
          left join stock_usuarios u on u.id_usu = m.mov_idusu
          LEFT JOIN productos pp ON pp.`id_producto` = p.`idproducto`
      WHERE m.`mov_id` =  '$id' and id_qrcode is not null ORDER BY movi_id DESC";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
       // var_dump($sql);
        return $data;
    }

    public static function movaddItem(pdo $db, $qr, $id, $idusu)
    {
        $sql = "SELECT p.id as id, id_qrcode, pp.unidad_medida, iddeposito, dep_idusu, tipoagrupacion, qr_code FROM app_productos p LEFT JOIN stock_qrcode qr ON p.`id_qr` = qr.`id_qrcode` RIGHT JOIN stock_depositos dep ON dep.`id_dep` = p.`iddeposito` left join productos pp on pp.`id_producto` = p.idproducto WHERE qr.qr_code = '" . $qr . "'  ";
        $stmt = $db->query($sql);
        if ($stmt->rowCount() == 0) {
            return array('code' => 200, 'status' => 'fail', 'message' => 'El Cod QR '.$qr. ' no existe en la base de datos', 'data' => '0');

        }
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        /*
        array(1) {
          [0]=>
          object(stdClass)#317 (7) {
            ["id"]=>
            int(5909)
            ["id_qrcode"]=>
            int(111)
            ["unidad_medida"]=>
            int(1)
            ["iddeposito"]=>
            int(139)
            ["dep_idusu"]=>
            int(256)
            ["tipoagrupacion"]=>
            string(1) "1"
            ["qr_code"]=>
            string(5) "2bead"
          }
        }
        */
        

        $idqr = $data[0]->id_qrcode; // 111
        $iddep1 = $data[0]->iddeposito; // 139
        $idusudest = $data[0]->dep_idusu; //256
        $idappproducto = $data[0]->id; // 5909
        $tipoagrupacion = $data[0]->tipoagrupacion; // 1 producto compuesto (0 componente)
        $unidad_medida = $data[0]->unidad_medida; // 1 unidades (2 ml 3 gramos)
        $qrcode = $data[0]->qr_code; // 2bead

        $sql = "SELECT * from stock_mov_items i left join stock_mov m on m.mov_id = i.movi_idmov left join stock_depositos dep on dep.id_dep = m.mov_iddep left join stock_usuarios u on dep.dep_idusu = u.id_usu WHERE i.movi_itemqr = '" . $qr . "' and i.movi_itemrecibido = '0'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        if ($stmt->rowCount() > 0) {
        $depnombre = $data[0]->dep_nombre;
        $agente = $data[0]->nombre_usu . " " . $data[0]->apellido_usu;
        $tel = $data[0]->telefono_usu;
            return array('code' => 200, 'status' => 'fail', 'message' => 'Este producto esta pendiente de recepcion. '."\r\n"."Deposito: ".$depnombre."\r\n"."Agente: ".$agente."\r\n"."Tel agente: ".$tel, 'data' => '0');
        }

        if ($tipoagrupacion == '0') {

            $insertar = "insert into `stock_mov_items` (`movi_itemqr`, `movi_idmov`) values ('" . $qrcode . "', '" . $id . "');";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idimov = $db->lastInsertId();

            /* INICIO LOG MOVIMIENTO DE MAQUINAS */
            $sql = "SELECT * FROM " . DB_BASE . ".stock_mov WHERE mov_id = '" . $id . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            $iddep2 = $data[0]->mov_iddep;

            $reg = array();
            $reg['idimov'] = $idimov;
            $reg['iddep1'] = $iddep1;
            $reg['iddep2'] = $iddep2;
            $reg['idusu'] = $idusudest;
            $reg['qr'] = $qr;
            $reg['lat'] = "";
            $reg['lon'] = "";
            $reg['precision'] = "";
            $reg['operacion'] = "-1";
            $idlog = Movimiento::mlog($db, $reg);
            /* FIN LOG MOVIMIENTO DE MAQUINAS */
        } else if ($tipoagrupacion == '1') {{
            $sql = "SELECT qr_code, p.id FROM app_componentes appcompo LEFT JOIN app_productos p ON p.id = idprod_compoapp LEFT JOIN stock_qrcode qr ON qr.id_qrcode = p.id_qr WHERE appcompo.idprodconjunto_compoapp = '" . $idappproducto . "'";
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $datafila = $stmt->fetchAll(PDO::FETCH_OBJ);

           

            /*
            array(4) {
              [0]=>
              object(stdClass)#318 (2) {
                ["qr_code"]=>
                string(5) "2bead"
                ["id"]=>
                int(5909)
              }
              [1]=>
              object(stdClass)#316 (2) {
                ["qr_code"]=>
                string(5) "b3018"
                ["id"]=>
                int(5911)
              }
              [2]=>
              object(stdClass)#319 (2) {
                ["qr_code"]=>
                string(5) "81bc2"
                ["id"]=>
                int(5912)
              }
              [3]=>
              object(stdClass)#320 (2) {
                ["qr_code"]=>
                string(5) "84beb"
                ["id"]=>
                int(5913)
              }
            }
            */
          
            // if ($stmt->rowCount() > 0) {

            //     return array('code' => 200, 'status' => 'fail', 'message' => 'No se pudo realizar la operacion', 'data' => '0');

            // }

            foreach ($datafila as $fila) {
                $insertar = "insert into `stock_mov_items` (`movi_itemqr`, `movi_idmov`) values ('" . $fila->qr_code . "', '" . $id . "');";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idimov = $db->lastInsertId();

                /* INICIO LOG MOVIMIENTO DE MAQUINAS */
                $sql = "SELECT * FROM " . DB_BASE . ".stock_mov WHERE mov_id = '" . $id . "'";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                $iddep2 = $data[0]->mov_iddep;

                $reg = array();
                $reg['idimov'] = $idimov;
                $reg['iddep1'] = $iddep1;
                $reg['iddep2'] = $iddep2;
                $reg['idusu'] = $idusudest;
                $reg['qr'] = $qr;
                $reg['lat'] = "";
                $reg['lon'] = "";
                $reg['precision'] = "";
                $reg['operacion'] = "-1";
                $idlog = Movimiento::mlog($db, $reg);
                /* FIN LOG MOVIMIENTO DE MAQUINAS */
            }
        }
        }

        $sql = "SELECT
            p.producto as prod_nombre,
            d.dep_nombre,
            u.nombre_usu,
            u.apellido_usu,
            a.app_fabricacion,
            a.app_lote,
            a.app_fechavencimiento,
            p.unidad_medida,
            p.tipoagrupacion,
            CASE
                    WHEN p.unidad_medida = '1' THEN 'Unidades'
                    WHEN p.unidad_medida = '2' THEN 'Mililitros'
                    WHEN p.unidad_medida = '3' THEN 'Gramos'
                END AS medida,
                a.app_cantidad_saldo,
                qr.qr_code
            FROM `app_productos` a

            left join productos p on p.id_producto = a.idproducto
            left join stock_depositos d on d.id_dep = a.iddeposito
            left join stock_usuarios u on u.id_usu = d.dep_idusu
            left join stock_qrcode qr on qr.id_qrcode = a.id_qr
            where qr.qr_code = '" . $qr . "' and qr.estado_qrcode = '1';";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $mensajeok = "Producto cargado con exito: " . $data[0]->prod_nombre . "\r\n";
        if ($data[0]->tipoagrupacion == '2') {
            $mensajeok .= "Cantidad: " . $data[0]->app_cantidad_saldo . " " . $data[0]->medida . "\r\n";
        }
        $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
        $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
        $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
        // $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
        // $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
        $mensajeok .= "QR: " . $data[0]->qr_code;

        return array('code' => 200, 'status' => 'success', 'message' => $mensajeok, 'data' => '0');

        //return $idimov;
    }

    public static function movdelItem(pdo $db, $id)
    {
        $sql = "SELECT * from " . DB_BASE . ".stock_mov_items WHERE movi_id = '" . $id . "' and movi_itemrecibido = '0'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {

            $sql = "DELETE FROM " . DB_BASE . ".`stock_mov_items` WHERE `movi_id` = '" . $id . "' and movi_itemrecibido = '0'";
            $stmt = $db->query($sql);

            $sql = "DELETE FROM " . DB_BASE . ".`log_movstock` WHERE `idimov_mlog` = '" . $id . "'";
            $stmt = $db->query($sql);
        }
        return true;
    }

    public static function comentarItem(pdo $db, $iditem, $comentario)
    {
        $sql = "update
        `stock_mov_items`
      set
        `movi_itemobs` = '" . $comentario . "'
      where `movi_id` = '" . $iditem . "';
      ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return true;
    }
}
