<?php
namespace Clases\Stock;
use \PDO;
class Series
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
     * @return void
     */
    public static function serieProducto(pdo $db, $idserie): array
    {
        $sql = "SELECT
        id_serie,
        descripcion_serie,
        qr_code,
        fecha_activacion,
        descripcion,
        p.nombrefoto,
        producto as prod_nombre,
        estado_producto,
        CASE
            WHEN estado_producto = '-1' THEN 'Desactivado'
            WHEN estado_producto = '0' THEN 'Sin uso'
            WHEN estado_producto = '1' THEN 'Operativa'
            WHEN estado_producto = '2' THEN 'En transito'
            WHEN estado_producto = '3' THEN 'En reparacion'
            ELSE '-'
        END prod_estado,
        dep_nombre,
        lat_gps,
        lon_gps
        FROM stock_qrcode_serie serie
        LEFT JOIN stock_qrcode cod ON serie.`id_serie` = cod.`idserie`
        LEFT JOIN app_productos p ON cod.`id_qrcode` = p.`id_qr`
        LEFT JOIN productos sp ON p.`idproducto` = sp.`id`
        LEFT JOIN stock_depositos dep ON p.`iddeposito` = dep.`id_dep`
        LEFT JOIN stock_ubi_gps gps ON gps.`id_gps` = p.`idgps`
        WHERE serie.`id_serie` = '" . $idserie . "' AND estado_qrcode = '1'";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
    public static function crearSerie(pdo $db, $form)
    {
        $sql = "INSERT INTO " . DB_BASE . ".`stock_qrcode_serie` (`descripcion_serie`, `talle`, `fecha_serie`, cantidad_serie) VALUES ('" . $form['descripcion'] . "', '" . $form['talle'] . "', '" . $form['fecha'] . "', '" . $form['cantidad'] . "');";
        $stmt = $db->query($sql);
        $idserie = $db->lastInsertId();
      
        $sql = '';
        for ($i = 1; $i <= $form['cantidad']; $i++) {
            $qr = substr(md5(rand()), 0, 6);
            $aux = array('qr' => $qr, 'idserie' => $idserie);
            $data[] = $aux;
        }
        $sql = 'INSERT INTO stock_qrcode(qr_code, idserie) VALUES(:qr, :idserie)';
        $sentencia = $db->prepare($sql);
        foreach ($data as $fila) {
            $sentencia->execute($fila);
        }
        return array('code' => 200, 'status' => 'success', 'message' => 'Operacion exitosa', 'data' => $idserie);
    }
    public static function agregarqr(pdo $db, $idserie, $cant)
    {
        $sql = '';
        for ($i = 1; $i <= $cant; $i++) {
            $qr = substr(md5(rand()), 0, 5);
            $aux = array('qr' => $qr, 'idserie' => $idserie);
            $data[] = $aux;
        }
        $sql = 'INSERT INTO stock_qrcode(qr_code, idserie) VALUES(:qr, :idserie)';
        $sentencia = $db->prepare($sql);
        foreach ($data as $fila) {
            $sentencia->execute($fila);
        }
        return true;
    }
    public static function getSeries(pdo $db): array
    {
        $sql = "SELECT
        id_serie idserie,
        descripcion_serie descripcion,
        talle,
        (SELECT COUNT(idserie) FROM stock_qrcode AS qrcode2 WHERE qrcode2.idserie = serie.id_serie AND qrcode2.estado_qrcode = '1') AS activos,
        (SELECT COUNT(idserie) FROM stock_qrcode AS qrcode2 WHERE qrcode2.idserie = serie.id_serie AND qrcode2.estado_qrcode = '0') AS inactivos,
        cantidad_serie AS total,
        serie.`fecha_serie`
        FROM stock_qrcode_serie AS serie ORDER BY fecha_serie DESC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
    public static function getQR(pdo $db, $idserie)
    {
        $sql = "SELECT id_qrcode FROM stock_qrcode qr WHERE qr.`estado_qrcode` = '0' AND qr.`idserie` = '$idserie' ORDER BY id_qrcode ASC LIMIT 0,1";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $idQR = $data[0]->id_qrcode;
        return $idQR;
    }
    public static function getSerie(pdo $db, $idserie): array
    {
        $sql = "SELECT qr_code FROM stock_qrcode_serie serie LEFT JOIN stock_qrcode qr ON serie.`id_serie` = qr.`idserie` WHERE qr.`estado_qrcode` = '0' AND serie.`id_serie` = '$idserie';";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
}
