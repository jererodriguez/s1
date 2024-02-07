<?php
namespace Clases\Stock;
error_reporting(0);
use \PDO;

// print_r($settings);
class Productos
{

    private $log;
    public function __construct($log = null)
    {
        $this->log = $log;
    }
    /**
     * Undocumented function
     *
     * @param [type] $db conexion de base de datos
     * @param array $verComlumnas si se quiere obtener una columna
     * @return void
     */
    public static function getProductos(pdo $db, $form): array
    {
        $sql = "SELECT id_producto as id, producto as prod_nombre ,prod_requiere_mantenimiento as prod_mant, descripcion as prod_descrip, id_estado_producto, tipoagrupacion, unidad_medida, CASE
        WHEN unidad_medida = '1' THEN 'Unidades'
        WHEN unidad_medida = '2' THEN 'Mililitros'
        WHEN unidad_medida = '3' THEN 'Gramos'
    END AS medida  FROM " . DB_BASE . ".productos where id_estado_producto = '1' " . $form['tipo'];
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
    public static function existeQR(pdo $db, $qr)
    {
        $sql = "SELECT * FROM stock_qrcode qr RIGHT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode` WHERE qr.qr_code = '" . $qr . "' AND qr.estado_qrcode = '1'";
        $stmt = $db->query($sql);
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        return $data;
    }
    public function insertarImg($db, $obj = [])
    {
        if (!count($obj)) {
            return 0;
        }
        $columnaAInsertar = "";
        $variableAInsertar = "";
        $bandera = 0;
        foreach ($obj as $key => $value) {
            if ($bandera == 0) {
                $bandera++;
                $columnaAInsertar .= $key;
                $variableAInsertar .= ":" . $key;
            } else {
                $columnaAInsertar .= "," . $key;
                $variableAInsertar .= ",:" . $key;
            }
        }
        $sql = "
            INSERT INTO " . DB_BASE . ".app_productos (
                $columnaAInsertar
            )
            VALUES (
                $variableAInsertar
            )
        ";
        $stmt = $db->prepare($sql);
        foreach ($obj as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        try {
            $stmt->execute();
            $id = $db->lastInsertId();
            return $id;
            //code...
        } catch (\Throwable$th) {
            if ($this->log) {
                ob_start();
                $stmt->debugDumpParams();
                $r = ob_get_contents();
                $this->log->error($r);
                ob_end_clean();
            }
            throw $th;
        }
    }
    public static function cargarProd(pdo $db, $id, $qr, $idDeposito, $descripcion, $lat, $lon, $precision, $ubicacion_hora, $estado, $idusu, $idexterno)
    {
        $fecha = date('Y-m-d H:i:s');
        $sql = "SELECT id_qrcode FROM stock_qrcode WHERE id_qrcode = '$qr' and estado_qrcode = '0'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idqr = $data[0]->id_qrcode;
        if (!$idqr = $data[0]->id_qrcode) {
            $sql = "SELECT id_qrcode FROM stock_qrcode WHERE qr_code = '$qr' and estado_qrcode = '0'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $idqr = $data[0]->id_qrcode;
        }
        $insertar = "INSERT INTO " . DB_BASE . ".`stock_ubi_gps` (`id_prod`, `lat_gps`, `lon_gps`, `precision_gps`, `hora_gps`, `hora_sistema`) VALUES ('0', '$lat', '$lon', '$precision', '$ubicacion_hora', '$fecha')";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $idgps = $db->lastInsertId();
        $sql = "update " . DB_BASE . ".`stock_qrcode` set `estado_qrcode` = '1', fecha_activacion = '$fecha', idusu = '$idusu', idgps = '$idgps' where id_qrcode = '$idqr'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $sql = "update
        `app_productos`
      set
        `idmaq` = '$idexterno',
        `descripcion` = '$descripcion',
        `id_qr` = '$idqr',
        `idDeposito` = '$idDeposito',
        `idgps` = '$idgps',
        `estado_producto` = '" . $estado . "'
      where `id` = '$id';
      ";
        $stmt = $db->query($sql);
        return $id;
    }
    public static function eliminarProd(pdo $db, $qr)
    {
        $sql = "SELECT p.id, id_qrcode from " . DB_BASE . ".app_productos p LEFT JOIN stock_qrcode qr ON p.`id_qr` = qr.`id_qrcode`  WHERE qr.qr_code = '" . $qr . "'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idProducto = $data[0]->id;
        $id_qrcode = $data[0]->id_qrcode;
        $sql = "update " . DB_BASE . ".`app_productos` set `estado_producto` = '-1' where `id` = '$idProducto';";
        $stmt = $db->query($sql);
        $sql = "update " . DB_BASE . ".`stock_qrcode` set `estado_qrcode` = '-1' where `id_qrcode` = '$id_qrcode';";
        $stmt = $db->query($sql);
        $sql = "DELETE FROM " . DB_BASE . ".`app_componentes` WHERE `idprod_compoapp` = '" . $idProducto . "'";
        $stmt = $db->query($sql);
        return true;
    }
    public static function insertCatalogo(pdo $db, $form)
    {
        $insertar = "
        INSERT INTO " . DB_BASE . ".productos (
            producto as prod_nombre,
            mov_cod_int,
            mov_cod_fab,
            prod_tienevencimiento,
            prod_mant,
            prod_descrip,
            prod_lat,
            prod_lon,
            prod_ubi_precision,
            prod_ubicacion_hora,
            tipoagrupacion,
            unidad_medida,
            id_estado_producto
        )
        VALUES
        (
            '" . $form['nombre'] . "',
            '" . $form['codint'] . "',
            '" . $form['codfab'] . "',
            '" . $form['tienevencimiento'] . "',
            '" . $form['mantenimiento'] . "',
            '" . $form['descripcion'] . "',
            '" . $form['lat'] . "',
            '" . $form['lon'] . "',
            '" . $form['precision'] . "',
            '" . $form['ubicacion_hora'] . "',
            '" . $form['tipoagrupacion'] . "',
            '" . $form['unidad_medida'] . "',
            '1'
        );
    ";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id = $db->lastInsertId();
        return $id;
    }
    public function updateProducto($db, $obj = [], $id)
    {
        // $sql = "UPDATE `comodin_stock`.`productos` SET `prod_foto`:prod_foto, `id_estado_producto` = '1' WHERE `id` = '105';";
        // $stmt = $db->prepare($sql);
        if (!count($obj)) {
            return 0;
        }
        $columnaActualizar = "";
        $bandera = 0;
        foreach ($obj as $key => $value) {
            if ($bandera == 0) {
                $bandera++;
                $columnaActualizar .= $key . "= :" . $key;
            } else {
                $columnaActualizar .= "," . $key . "= :" . $key;
            }
        }
        $sql = "
            UPDATE " . DB_BASE . ".productos  SET
               $columnaActualizar
            WHERE id = $id
        ";
        $stmt = $db->prepare($sql);
        foreach ($obj as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        try {
            $stmt->execute();
            // $id = $db->rowCount();
            return 1;
            //code...
        } catch (\Throwable$th) {
            if ($this->log) {
                ob_start();
                $stmt->debugDumpParams();
                $r = ob_get_contents();
                $this->log->error($r);
                ob_end_clean();
            }
            throw $th;
        }
    }
    public function insertCatalogoweb($db, $obj = [])
    {
        if (!count($obj)) {
            return 0;
        }
        $columnaAInsertar = "";
        $variableAInsertar = "";
        $bandera = 0;
        foreach ($obj as $key => $value) {
            if ($bandera == 0) {
                $bandera++;
                $columnaAInsertar .= $key;
                $variableAInsertar .= ":" . $key;
            } else {
                $columnaAInsertar .= "," . $key;
                $variableAInsertar .= ",:" . $key;
            }
        }
        $sql = "
            INSERT INTO " . DB_BASE . ".productos (
                $columnaAInsertar
            )
            VALUES (
                $variableAInsertar
            )
        ";
        $stmt = $db->prepare($sql);
        foreach ($obj as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        //var_dump($stmt);
        //  die();
        try {
            $stmt->execute();
            $id = $db->lastInsertId();
            return $id;
            //code...
        } catch (\Throwable$th) {
            if ($this->log) {
                ob_start();
                $stmt->debugDumpParams();
                $r = ob_get_contents();
                $this->log->error($r);
                ob_end_clean();
            }
            throw $th;
        }
    }
    public function updateCatalogoweb($db, $obj = [])
    {

        $sql = "UPDATE productos SET ";
        if ($obj['prod_foto'] !== null) {
            $sql .= "prod_foto = '" . $obj['prod_foto'] . "',";
        }
        $sql .= "
        producto as prod_nombre = '" . $obj['producto as prod_nombre'] . "',
        id_estado_producto  = '" . $obj['id_estado_producto'] . "',
        tipoagrupacion = '" . $obj['tipoagrupacion'] . "',
        prod_tienevencimiento = '" . $obj['prod_tienevencimiento'] . "',
        mant_asistencia = '" . $obj['mant_asistencia'] . "',
        mov_cod_fab = '" . $obj['mov_cod_fab'] . "',
        mov_cod_int = '" . $obj['mov_cod_int'] . "',
        prod_descrip = '" . $obj['prod_descrip'] . "'
                                WHERE id='" . $obj['idProducto'] . "'";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $id = $db->lastInsertId();
            return $id;
            //code...
        } catch (\Throwable$th) {
            if ($this->log) {
                ob_start();
                $stmt->debugDumpParams();
                $r = ob_get_contents();
                $this->log->error($r);
                ob_end_clean();
            }
            throw $th;
        }
    }

    public static function etiquetarProdGranel(pdo $db, $form): array
    {

        $fecha = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM stock_qrcode WHERE id_qrcode = '" . $form['codqr'] . "'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idqr = $data[0]->id_qrcode;
        if (!$idqr = $data[0]->id_qrcode) {
            $sql = "SELECT * FROM stock_qrcode WHERE qr_code = '" . $form['codqr'] . "'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $idqr = $data[0]->id_qrcode;
        }

        if ($idqr) {
            if ($data[0]->estado_qrcode == '0') {
                $insertar = "INSERT INTO " . DB_BASE . ".`stock_ubi_gps` (`id_prod`, `lat_gps`, `lon_gps`, `precision_gps`, `hora_gps`, `hora_sistema`) VALUES ('0', '" . $form['codqr'] . "', '" . $form['lon'] . "', '" . $form['precision'] . "', '" . $form['ubicacion_hora'] . "', '$fecha')";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idgps = $db->lastInsertId();
                $sql = "update " . DB_BASE . ".`stock_qrcode` set `estado_qrcode` = '1', fecha_activacion = '$fecha', idusu = '" . $form['idusu'] . "', idgps = '$idgps' where id_qrcode = '$idqr'";
                $stmt = $db->prepare($sql);
                $stmt->execute();

                $insertar = "INSERT INTO " . DB_BASE . ".`app_productos` (`idproducto`, `idmaq`, `descripcion`, `id_qr`, `idDeposito`, `idgps`, `estado_producto`, app_fabricacion, app_fechavencimiento, app_lote, app_cantidad_saldo ) VALUES ('" . $form['idproducto'] . "', '" . $form['codfabricante'] . "', '" . $form['comentario'] . "', '" . $idqr . "','" . $form['iddeposito'] . "','" . $idgps . "','0', '" . $form['fechadefabricacion'] . "','" . $form['fechadevencimiento'] . "','" . $form['lote'] . "','" . $form['cantidad'] . "')";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idprod = $db->lastInsertId();

                $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('0', '" . $idprod . "', '" . $idprod . "')";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idcompo = $db->lastInsertId();

                $sql = "SELECT
            p.producto as prod_nombre,
            d.dep_nombre,
            u.nombre_usu,
            u.apellido_usu,
            a.app_fabricacion,
            a.app_lote,
            a.app_fechavencimiento,
            p.unidad_medida,
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
            where a.id = '" . $idprod . "' and qr.estado_qrcode = '1';";
                $stmt = $db->query($sql);
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                $mensajeok = "Producto cargado con exito: " . $data[0]->prod_nombre . "\r\n";
                $mensajeok .= "Cantidad: " . $data[0]->app_cantidad_saldo . " " . $data[0]->medida . "\r\n";
                $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
                $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
                $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
                $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
                $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
                $mensajeok .= "QR: " . $data[0]->qr_code;

                return array('code' => 200, 'status' => 'success', 'message' => $mensajeok, 'data' => $idprod);
            } else {
                $sql = "SELECT
            p.producto as prod_nombre,
            d.dep_nombre,
            u.nombre_usu,
            u.apellido_usu,
            a.app_fabricacion,
            a.app_lote,
            a.app_fechavencimiento,
            p.unidad_medida,
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
            where a.id_qr = '" . $idqr . "'";
                $stmt = $db->query($sql);
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                $mensajeok = "\r\n" . "NO SE COMPLETO LA OPERACION. EL CODIGO QR YA SE ENCUENTRA EN USO POR EL SIGUIENTE PRODUCTO: " . "\r\n" . $data[0]->prod_nombre . "\r\n";
                $mensajeok .= "Cantidad: " . $data[0]->app_cantidad_saldo . " " . $data[0]->medida . "\r\n";
                $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
                $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
                $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
                $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
                $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
                $mensajeok .= "QR: " . $data[0]->qr_code;

                return array('code' => 200, 'status' => 'fail', 'message' => $mensajeok, 'data' => '');
            }

        } else {
            return array('code' => 500, 'status' => 'fail', 'message' => 'El Codigo QR existe.', 'data' => '');
        }
    }

    public static function fraccionarGranel(pdo $db, $form): array
    {
        $hoy = date('Y-m-d H:i:s');
        $sql = "SELECT
        a.id,
        d.id_dep,
        p.id_producto as prod_id,
        p.producto as prod_nombre,
        d.dep_nombre,
        u.nombre_usu,
        u.apellido_usu,
        a.app_fabricacion,
        a.app_lote,
        a.app_fechavencimiento,
        p.unidad_medida,
        CASE
                WHEN p.unidad_medida = '1' THEN 'Unidades'
                WHEN p.unidad_medida = '2' THEN 'Mililitros'
                WHEN p.unidad_medida = '3' THEN 'Gramos'
            END AS medida,
            a.app_cantidad_saldo,
            qr.qr_code,
            a.descripcion
        FROM `app_productos` a

        left join productos p on p.id_producto = a.idproducto
        left join stock_depositos d on d.id_dep = a.iddeposito
        left join stock_usuarios u on u.id_usu = d.dep_idusu
        left join stock_qrcode qr on qr.id_qrcode = a.id_qr
        where qr.qr_code = '" . $form['qr'] . "'  and  qr.estado_qrcode = '1' and p.unidad_medida != '1'";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (!$data[0]) {
            return array('code' => 200, 'status' => 'fail', 'message' => 'Codigo QR no existe', 'data' => "");
        }
        if ($data[0]->app_cantidad_saldo > 0) {
            return array('code' => 200, 'status' => 'success', 'message' => 'Operacion exitosa', 'data' => $data[0]);
            $now = time(); // or your date as well
            $your_date = strtotime($data[0]->app_fechavencimiento);
            $datediff = $now - $your_date;
            $dias = round($datediff / (60 * 60 * 24));
            if ($dias <= 0) {
                return array('code' => 200, 'status' => 'fail', 'message' => 'El producto vencio hace ' . $dias . ' dias', 'data' => "");
            }
        } else {
            return array('code' => 200, 'status' => 'fail', 'message' => 'Saldo insuficiente o envase vacio', 'data' => "");
        }

        // and a.app_cantidad_saldo > 0 and a.app_fechavencimiento >= '" . $hoy . "'

    }

    public static function etiquetarFraccionaagranel(pdo $db, $form): array
    {
        $fecha = date('Y-m-d H:i:s');

        $sql = "SELECT
        p.producto as prod_nombre,
        d.dep_nombre,
        u.nombre_usu,
        u.apellido_usu,
        a.app_fabricacion,
        a.app_lote,
        a.app_fechavencimiento,
        p.unidad_medida,
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
        where a.id = '" . $form['idprodconjunto'] . "' and qr.estado_qrcode = '1';";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $saldo = $data[0]->app_cantidad_saldo;

        $updatesaldo = $saldo - $form['cantidad'];

        if ($updatesaldo < 0) {
            $mensajeok = "NO SE PUDO RETIRAR: " . $data[0]->prod_nombre . "\r\n";
            $mensajeok .= "CANTIDAD REQUERIDA: " . $form['cantidad'] . " " . $data[0]->medida . "\r\n";
            $mensajeok .= "SALDO: " . $saldo . " " . $data[0]->medida . "\r\n";
            $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
            $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
            $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
            $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
            $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
            $mensajeok .= "QR: " . $data[0]->qr_code;
            return array('code' => 500, 'status' => 'success', 'message' => $mensajeok, 'saldo' => $saldo);
        }

        $sql = "SELECT id_qrcode FROM stock_qrcode WHERE id_qrcode = '" . $form['codqr'] . "' and estado_qrcode = '0'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idqr = $data[0]->id_qrcode;
        if (!$idqr = $data[0]->id_qrcode) {
            $sql = "SELECT id_qrcode FROM stock_qrcode WHERE qr_code = '" . $form['codqr'] . "' and estado_qrcode = '0'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $idqr = $data[0]->id_qrcode;
        }

        if ($idqr) {

            $insertar = "INSERT INTO " . DB_BASE . ".`stock_ubi_gps` (`id_prod`, `lat_gps`, `lon_gps`, `precision_gps`, `hora_gps`, `hora_sistema`) VALUES ('0', '" . $form['codqr'] . "', '" . $form['lon'] . "', '" . $form['precision'] . "', '" . $form['ubicacion_hora'] . "', '$fecha')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idgps = $db->lastInsertId();
            $sql = "update " . DB_BASE . ".`stock_qrcode` set `estado_qrcode` = '1', fecha_activacion = '$fecha', idusu = '" . $form['idusu'] . "', idgps = '$idgps' where id_qrcode = '$idqr'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $insertar = "INSERT INTO " . DB_BASE . ".`app_productos` (`idproducto`, `idmaq`, `descripcion`, `id_qr`, `idDeposito`, `idgps`, `estado_producto`, app_fabricacion, app_fechavencimiento, app_lote, app_cantidad_saldo ) VALUES ('" . $form['prodid'] . "', '" . $form['codfabricante'] . "', '" . $form['comentario'] . "', '" . $idqr . "','" . $form['iddeposito'] . "','" . $idgps . "','0', '" . $form['fechadefabricacion'] . "','" . $form['fechadevencimiento'] . "','" . $form['lote'] . "','" . $form['cantidad'] . "')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idprod = $db->lastInsertId();

            $sql = "UPDATE `app_productos` SET `app_cantidad_saldo` = `app_cantidad_saldo` - " . $form['cantidad'] . " WHERE `app_productos`.`id` = '" . $form['idprodconjunto'] . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $sql = "SELECT app_cantidad_saldo as saldo FROM `app_productos` WHERE `app_productos`.`id` = '" . $form['idprodconjunto'] . "'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $saldo = $data[0]->saldo;

            $sql = "SELECT * FROM `app_componentes` where idprod_compoapp = idprodconjunto_compoapp and idprod_compoapp = '" . $form['idprodconjunto'] . "'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $id_compo = $data[0]->id_compoapp;
            if (!$id_compo) {
                $delete = "DELETE FROM `app_componentes` WHERE idprod_compoapp ='" . $form['idprodconjunto'] . "'";
                $stmt = $db->prepare($delete);
                $stmt->execute();

                $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('0', '" . $form['idprodconjunto'] . "', '" . $form['idprodconjunto'] . "')";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idcompo = $db->lastInsertId();
            }

            $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('0', '" . $idprod . "', '" . $form['idprodconjunto'] . "')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idcompo = $db->lastInsertId();

            $sql = "SELECT
            p.producto as prod_nombre,
            d.dep_nombre,
            u.nombre_usu,
            u.apellido_usu,
            a.app_fabricacion,
            a.app_lote,
            a.app_fechavencimiento,
            p.unidad_medida,
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
            where a.id = '" . $idprod . "' and qr.estado_qrcode = '1';";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $mensajeok = "Producto cargado con exito: " . $data[0]->prod_nombre . "\r\n";
            $mensajeok .= "Cantidad empaquetada: " . $data[0]->app_cantidad_saldo . " " . $data[0]->medida . "\r\n";
            $mensajeok .= "Saldo: " . $saldo . " " . $data[0]->medida . "\r\n";
            $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
            $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
            $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
            $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
            $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
            $mensajeok .= "QR: " . $data[0]->qr_code;

            return array('code' => 200, 'status' => 'success', 'message' => $mensajeok, 'saldo' => $saldo);

        } else {
            return array('code' => 500, 'status' => 'fail', 'message' => 'El Codigo QR no es valido', 'data' => '');
        }
    }

    public static function etiquetarComponentes(pdo $db, $form): array
    {
        $fecha = date('Y-m-d H:i:s');
        $sql = "SELECT id_qrcode FROM stock_qrcode WHERE id_qrcode = '" . $form['codqr'] . "' and estado_qrcode = '0'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idqr = $data[0]->id_qrcode;
        if (!$idqr = $data[0]->id_qrcode) {
            $sql = "SELECT id_qrcode FROM stock_qrcode WHERE qr_code = '" . $form['codqr'] . "' and estado_qrcode = '0'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $idqr = $data[0]->id_qrcode;
        }

        if ($idqr) {

            /* inicio crear conjunto del idproducto compuesto */

            $sql = "SELECT * FROM `productos` WHERE id_producto = '" . $form['idproducto'] . "' and tipoagrupacion = '1'";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            if ($data) {
                $sql = "SELECT compo.id_compo FROM `stock_productos_conjunto` conjunto  left join stock_productos_cat cat on conjunto.id_conjunto = cat.idconjunto_cat left join stock_productos_compo compo on compo.idcat_compo = cat.id_cat where conjunto.idprod_conjunto = '" . $form['idproducto'] . "' and cat.idpadre_cat = '0' and compo.idprod_compo = '" . $form['idproducto'] . "'";
                $stmt = $db->query($sql);
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                $idcompo = $data[0]->id_compo;
                if (!$idcompo) {
                    return array('code' => 500, 'status' => 'fail', 'message' => 'Esta intentando cargar un producto compuesto sin matriz de componentes. Por favor contacte con el soporte tecnico. ', 'data' => '');
                }
            }

            /* fin crear conjunto del idproducto compuesto */

            $insertar = "INSERT INTO " . DB_BASE . ".`stock_ubi_gps` (`id_prod`, `lat_gps`, `lon_gps`, `precision_gps`, `hora_gps`, `hora_sistema`) VALUES ('0', '" . $form['codqr'] . "', '" . $form['lon'] . "', '" . $form['precision'] . "', '" . $form['ubicacion_hora'] . "', '$fecha')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idgps = $db->lastInsertId();
            $sql = "update " . DB_BASE . ".`stock_qrcode` set `estado_qrcode` = '1', fecha_activacion = '$fecha', idusu = '" . $form['idusu'] . "', idgps = '$idgps' where id_qrcode = '$idqr'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $insertar = "INSERT INTO " . DB_BASE . ".`app_productos` (`idproducto`, `idmaq`, `descripcion`, `id_qr`, `idDeposito`, `idgps`, `estado_producto`, app_fabricacion, app_fechavencimiento, app_lote, app_cantidad_saldo ) VALUES ('" . $form['idproducto'] . "', '" . $form['codfabricante'] . "', '" . $form['comentario'] . "', '" . $idqr . "','" . $form['iddeposito'] . "','" . $idgps . "','" . $form['estado'] . "', '" . $form['fechadefabricacion'] . "','" . $form['fechadevencimiento'] . "','" . $form['lote'] . "','" . $form['cantidad'] . "')";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idprod = $db->lastInsertId();

            if (@$idcompo) {
                $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('" . $idcompo . "', '" . $idprod . "', '" . $idprod . "')";
                $stmt = $db->prepare($insertar);
                $stmt->execute();
                $idcompo = $db->lastInsertId();
            }

            $sql = "SELECT
            p.producto as prod_nombre,
            d.dep_nombre,
            u.nombre_usu,
            u.apellido_usu,
            a.app_fabricacion,
            a.app_lote,
            a.app_fechavencimiento,
            a.estado_producto,
            CASE
                    WHEN a.estado_producto = '-1' THEN 'Desechar'
                    WHEN a.estado_producto = '0' THEN 'Disponible'
                    WHEN a.estado_producto = '1' THEN 'Operativo'
                    WHEN a.estado_producto = '2' THEN 'Transito'
                    WHEN a.estado_producto = '3' THEN 'Reparacion'
                END AS prod_estado,
            p.unidad_medida,
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
            where a.id = '" . $idprod . "' and qr.estado_qrcode = '1';";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $mensajeok = "Producto cargado con exito: " . $data[0]->prod_nombre . "\r\n";
            // $mensajeok .= "Cantidad: " . $data[0]->app_cantidad_saldo . " " . $data[0]->medida . "\r\n";
            $mensajeok .= "Fecha de fabricacion: " . $data[0]->app_fabricacion . "\r\n";
            $mensajeok .= "Lote: " . $data[0]->app_lote . "\r\n";
            $mensajeok .= "Estado: " . $data[0]->prod_estado . "\r\n";
            $mensajeok .= "Fecha de Vencimiento: " . $data[0]->app_fechavencimiento . "\r\n";
            $mensajeok .= "Deposito: " . $data[0]->dep_nombre . "\r\n";
            $mensajeok .= "Agente: " . $data[0]->nombre_usu . " " . $data[0]->apellido_usu . "\r\n";
            $mensajeok .= "QR: " . $data[0]->qr_code;

            return array('code' => 200, 'status' => 'success', 'message' => $mensajeok, 'data' => $idprod);

        } else {
            return array('code' => 500, 'status' => 'fail', 'message' => 'El Codigo QR no es valido', 'data' => '');
        }
    }

    public static function estadoqr(pdo $db, $qrcode): array
    {
        $sql = "SELECT qr_code, producto as prod_nombre, pp.descripcion as prod_descrip, nombrefoto, dep_ciudad, dep_nombre, dep_telefono, nombre_usu, apellido_usu, telefono_usu, estado_producto, p.descripcion, CASE
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
            $mensajeok = "El QR ya se encuentra en uso por: " . "\r\n";
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
            return array('code' => 1, 'status' => 'fail', 'message' => $mensajeok, 'data' => '');
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

            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeok, 'data' => '');

        }
        if (!$data) {
            return array('code' => 404, 'status' => 'fail', 'message' => 'El Cod. QR no existe', 'data' => '');
        }
    }

}
