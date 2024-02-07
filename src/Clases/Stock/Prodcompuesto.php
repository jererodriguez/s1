<?php

namespace Clases\Stock;

use \PDO;

class Prodcompuesto
{
    public static function getRuta(pdo $db, $idcat)
    {
        $ruta = array();
        do {
            $sql = "SELECT id_cat, idconjunto_cat, idpadre_cat, idprod_cat, producto as prod_nombre FROM stock_productos_cat cat LEFT JOIN productos p on p.id_producto = cat.`idprod_cat` WHERE cat.`id_cat` = '" . $idcat . "';";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
      
            $idcat = (int) $data->idpadre_cat;
            $datos = (array) $data;
            $ruta[] = $datos;

        } while ($idcat > 0);
        sort($ruta);
        return $ruta;
    }

    public static function crearCategoria(pdo $db, $idcompo)
    {

        $sql = "SELECT * FROM stock_productos_compo compo RIGHT JOIN stock_productos_cat cat ON compo.`idcat_compo` = cat.id_cat left join app_productos p on p.idproducto = idprod_compo left join productos pp on pp.id_producto = p.idproducto WHERE compo.`id_compo` = '" . $idcompo . "' and pp.unidad_medida = '1' ";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $insertar = "INSERT INTO `stock_productos_cat` (`idconjunto_cat`, `idprod_cat`, `idpadre_cat`) VALUES ('" . $data->idconjunto_cat . "', '" . $data->idprod_compo . "', '" . $data->idcat_compo . "');";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idcat = $db->lastInsertId();


            $sql = "UPDATE `stock_productos_compo` SET `idcat_compo` = '" . $idcat . "' WHERE `id_compo` = '" . $idcompo . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }

        return true;
    }


    public static function crearConjunto(pdo $db, $idProducto)
    {

        $sql = "SELECT  id_conjunto AS idConjunto, id_cat AS idCategoria
        FROM stock_productos_conjunto conjunto
        RIGHT JOIN stock_productos_cat cat ON cat.`idprod_cat` = conjunto.`idprod_conjunto` WHERE idprod_conjunto = '" . $idProducto . "'";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            return $data;
        }

        $id = array();

        $sql = "UPDATE `productos` SET `tipoagrupacion` = '1' WHERE `id_producto` = '" . $idProducto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $insertar = "INSERT INTO `stock_productos_conjunto` (`idprod_conjunto`) VALUES ('" . $idProducto . "');";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id['idConjunto'] = $db->lastInsertId();

        $insertar = "INSERT INTO `stock_productos_cat` (`idconjunto_cat`, `idprod_cat`, `idpadre_cat`) VALUES ('" . $id['idConjunto']  . "', '" . $idProducto . "', '0');";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        $id['idCategoria'] = $db->lastInsertId();

        $insertar = "INSERT INTO `stock_productos_compo` (`idcat_compo`, `idprod_compo`) VALUES ('" . $id['idCategoria'] . "', '" . $idProducto . "'); ";
        $stmt = $db->prepare($insertar);
        $stmt->execute();
        //$id['idComponente'] = $db->lastInsertId();

        return $id;
    }

    public static function addCompo(pdo $db, $idCategoria, $idProducto)
    {
        $idCompo = 0;
        $sql = "SELECT * FROM stock_productos_compo compo WHERE idcat_compo = '" . $idCategoria . "' AND idprod_compo = '" . $idProducto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $insertar = "insert into `stock_productos_compo` (`idcat_compo`, `idprod_compo`) values ('" . $idCategoria . "', '" . $idProducto . "');";
            $stmt = $db->prepare($insertar);
            $stmt->execute();
            $idCompo = $db->lastInsertId();
        }

        return $idCompo;
    }



    public static function getConjuntos(pdo $db)
    {
        $sql = "SELECT id_producto AS idproducto, IF(id_conjunto, id_conjunto, 0) idconjunto, IF(id_cat, id_cat, 0) idcat, IF(idpadre_cat, idpadre_cat, 0) idpadrecat, producto as prod_nombre, 'pic.png' as prod_foto, p.`descripcion`
        FROM productos p
                LEFT JOIN stock_productos_conjunto conjunto ON conjunto.idprod_conjunto = p.id_producto
                LEFT JOIN stock_productos_cat cat ON cat.idprod_cat = p.id_producto
                WHERE p.`unidad_medida` = '1' ORDER BY producto ASC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function getallCompo(pdo $db): array
    {   
        $sql = " SELECT id_producto AS idproducto, producto as prod_nombre, 'pic.jpg' as prod_foto, p.`descripcion` as prod_descrip
FROM productos p
        WHERE p.`id_estado_producto` = '1' and tipoagrupacion != '1' ORDER BY producto ASC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }

    public static function getsubCategorias(pdo $db, $idConjunto, $idPadrecat, $idcat)
    {
        $sql = "SELECT id_cat, idconjunto_cat, idprod_cat, producto as prod_nombre, p.descripcion as prod_descrip, 'pic.jpg' as prod_foto, idpadre_cat FROM stock_productos_cat cat LEFT JOIN productos p on p.id_producto = cat.`idprod_cat` WHERE cat.`idconjunto_cat` = '" . $idConjunto . "' AND cat.`idpadre_cat` = '" . $idcat . "' AND p.`id_estado_producto` = '1'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
//print_r($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }

    public static function getcatcomponentes(pdo $db, $idcat)
    {
        $sql = "SELECT id_compo, idprod_compo, p.producto as prod_nombre, p.descripcion as prod_descrip, 'pic.jpg' as prod_foto, pp.producto as catnombre FROM stock_productos_compo compo
        LEFT JOIN productos p on p.id_producto = compo.idprod_compo
        LEFT JOIN stock_productos_cat cat ON idcat_compo = cat.id_cat
        LEFT JOIN productos pp ON pp.`id_producto` = cat.idprod_cat
        WHERE idcat_compo = '" . $idcat . "' and cat.idprod_cat != compo.idprod_compo AND p.id_estado_producto = '1' ";

        //var_dump($sql);
        $stmt = $db->prepare($sql);
        $stmt->execute();

        // if ($stmt->rowCount() == 0) {
        //     return false;
        // }

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }

    public static function delcompo(pdo $db, $idcompo)
    {
        $sql = "SELECT * FROM stock_productos_compo compo RIGHT JOIN stock_productos_cat cat ON cat.`idprod_cat` = compo.`idprod_compo` WHERE id_compo = '$idcompo'";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {

            $sql = "DELETE FROM `stock_productos_compo` WHERE `id_compo` = '" . $idcompo . "'";
            $stmt = $db->query($sql);
            return true;
        } else {
            return false;
        }
    }

    public static function delCat(pdo $db, $idcat)
    {
        $sql = "DELETE FROM `stock_productos_cat` WHERE `id_cat` = '" . $idcat . "'";
        $stmt = $db->query($sql);

        $sql = "DELETE FROM `stock_productos_cat` WHERE `idpadre_cat` = '" . $idcat . "'";
        $stmt = $db->query($sql);

        $sql = "DELETE FROM `stock_productos_compo` WHERE `idcat_compo` = '" . $idcat . "'";
        $stmt = $db->query($sql);

        return true;
    }
}
