<?php

namespace Clases\Stock;

use \PDO;

class Caja
{
    public static function getProductos(pdo $db): array
    {

        $sql = "SELECT
        p.id_producto AS id,
        p.producto,
        p.precio,
        p.precio_mayorista,
        p.iva,
        p.unidad_medida,
        um.nombre_medida,
        um.simbolo,
        COALESCE(total_stock.stock, 0) AS total_stock,
        COALESCE(MAX(fotos.foto), '') AS ultima_foto,
        GROUP_CONCAT(cat.categoria SEPARATOR ', ') AS categoria
    FROM
        productos p
    LEFT JOIN (
        SELECT
            id_producto,
            SUM(stock) AS stock
        FROM
            deposito
        WHERE
            id_local = '1'
        GROUP BY
            id_producto
    ) total_stock ON total_stock.id_producto = p.id_producto
    LEFT JOIN productos_categorias pc ON pc.id_producto = p.id_producto
    LEFT JOIN categorias cat ON cat.id_categoria = pc.id_categoria
    LEFT JOIN (
        SELECT
            id_producto,
            foto
        FROM
            productos_fotos
        WHERE
            estado = '1'
        GROUP BY
            id_producto
        ORDER BY
            MAX(id_foto) DESC
    ) fotos ON fotos.id_producto = p.id_producto
    LEFT JOIN unidad_medidas um on um.id_medida = p.unidad_medida
    LEFT JOIN promociones t ON t.id_producto = p.id_producto
        AND CURDATE() BETWEEN inicio_promo AND fin_promo
        AND (COALESCE(total_stock.stock, 0) > 0 OR p.produccion > 0)
        AND p.producto LIKE '%'
    WHERE
        p.produccion != 1  -- Agregamos la condición para excluir productos con produccion = 1
    GROUP BY
        p.id_producto;
    ";


       
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }

    public static function RowMaestro(pdo $db, $tabla, $campo, $id, $campo2 = "", $id2 = "", $campo3 = "", $id3 = "")
    {
        if (!empty($tabla) and !empty($campo) and !empty($id)) {
            if (!empty($campo2) and !empty($id2)) {
                $q = "select * from $tabla where $campo='$id' and $campo2='$id2'";
            } else {
                $q = "select * from $tabla where $campo='$id' limit 1";
            }

            if (!empty($campo3) and !empty($id3) and !empty($campo2) and !empty($id2)) {
                $q = "select * from $tabla where $campo='$id' and $campo2='$id2' and $campo3='$id3'";
            }
            var_dump($q);
            $stmt = $db->query($q);
            $retorno = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $retorno;
        }
    }
}
