<?php

namespace Clases\Stock;

use \PDO;

class Reportes
{
    private $log;
    function __construct($log = null)
    {
        $this->log = $log;
    }

    /**
     * Undocumented function
     *
     * @param [type] $db conexion de base de datos
     * @return void
     */
    public static function getStock(pdo $db): array
    {
        $sql = "SELECT depositos.`dep_nombre`,
        catalogo.`producto as prod_nombre`,
        catalogo.`prod_foto`,
        catalogo.`prod_descrip`,
        COUNT(IF(productos.estado_producto = 0,1,NULL)
        ) AS disponibles,
        COUNT(IF(productos.estado_producto = 1,1,NULL)
        ) usados,
        COUNT(productos.id) total FROM app_productos AS productos
        LEFT JOIN stock_depositos AS depositos
          ON (
            depositos.`id_dep` = productos.`iddeposito`
          )
        LEFT JOIN productos AS catalogo
          ON (
            productos.idproducto = catalogo.id
          )
        LEFT JOIN stock_qrcode AS qr ON qr.id_qrcode = productos.id_qr
        WHERE qr.estado_qrcode = '1'
          
        GROUP BY productos.`iddeposito`,
        productos.`idproducto`
        
        ORDER BY producto as prod_nombre ASC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function getMaquinas(pdo $db): array
    {
        $sql = "SELECT
        qr.id_qrcode,
        sp.`producto as prod_nombre`,
        qr.qr_code,
        p.`idproducto`,
        p.`descripcion`,
        p.`nombrefoto`,
        p.iddeposito,
        dep_nombre,
        CONCAT(nombre_usu,' ',apellido_usu) AS usuario,
        qr.fecha_activacion
        
        
        FROM stock_qrcode qr
        LEFT JOIN app_productos p ON p.`id_qr` = qr.`id_qrcode`
        LEFT JOIN productos sp ON sp.id = p.`idproducto`
        LEFT JOIN stock_depositos dep ON dep.`id_dep` = p.`iddeposito`
        LEFT JOIN stock_usuarios usu ON usu.id_usu = qr.idusu
        WHERE estado_qrcode = '1' AND fecha_activacion IS NOT NULL ORDER BY fecha_activacion DESC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
}
