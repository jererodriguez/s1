<?php
namespace Clases\Stock;

use Clases\Stock\ServicioTecnico;
use \PDO;

class Appcomponentes
{

    public static function addcompobyqr(pdo $db, $idprodconjunto, $idcompo, $qrcode, $idusu)
    {
        $sql  = "SELECT p.id, p.idproducto, p.iddeposito, dep.dep_nombre, pp.producto as prod_nombre from app_productos p left join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos pp on pp.`id_producto` = p.idproducto left join stock_depositos dep on dep.id_dep = p.iddeposito where qr.qr_code = '" . $qrcode . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data          = $stmt->fetch(PDO::FETCH_OBJ);
       
        /*
            qr b3018
            object(stdClass)#317 (5) {
                ["id"]=>
                int(5911)
                ["idproducto"]=>
                int(5)
                ["iddeposito"]=>
                int(139)
                ["dep_nombre"]=>
                string(8) "Dep Jere"
                ["prod_nombre"]=>
                string(14) "Harina de maiz"
            }`
        */
        

        $iddep2        = $data->iddeposito ?? 0; //139
        $iddep2_nombre = $data->dep_nombre ?? 0; //dep jere
        $idprodnew     = $data->id ?? 0; //5911
        $idproductonew = $data->idproducto ?? 0; //5
        $prodnombrenew = $data->prod_nombre ?? 0; //Harina de maiz

        $sql  = "SELECT pp.`id_producto`, pp.producto as prod_nombre  FROM `stock_productos_compo` compo left join productos pp on pp.`id_producto` = compo.idprod_compo WHERE compo.`id_compo` = '" . $idcompo . "';";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info       = $stmt->fetch(PDO::FETCH_OBJ);
        
        /*
            object(stdClass)#316 (2) {
            ["id_producto"]=>
            int(5)
            ["prod_nombre"]=>
            string(14) "Harina de maiz"
            }
        */
        
        $idproducto = $info->id_producto ?? 0; //5
        $prodnombre = $info->prod_nombre ?? 0; //Harina de maiz
        if ($idproducto !== $idproductonew) {
            $mensajeerror = 'Esta procurando asignar un componente ' . $prodnombrenew . ' en un componente ' . $prodnombre . ' verifique que esta reemplazando la pieza correcta.';
            return array('code' => -1, 'status' => 'fail', 'message' => $mensajeerror, 'data' => '');
        }

        $sql  = "SELECT p.id, p.idproducto, pp.producto as prod_nombre, qr.qr_code, dep.dep_nombre, dep.id_dep from app_productos p left join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos pp on pp.`id_producto` = p.idproducto left join stock_depositos dep on dep.id_dep = p.iddeposito where p.id = '" . $idprodconjunto . "'";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info                   = $stmt->fetch(PDO::FETCH_OBJ);

        /*
            object(stdClass)#318 (6) {
            ["id"]=>
            int(5909)
            ["idproducto"]=>
            int(6)
            ["prod_nombre"]=>
            string(15) "Maquina de cafe"
            ["qr_code"]=>
            string(5) "2bead"
            ["dep_nombre"]=>
            string(8) "Dep Jere"
            ["id_dep"]=>
            int(139)
            }
        */

        $prodconjunto_iddep     = $info->id_dep ?? 0; //139
        $prodconjunto_depnombre = $info->dep_nombre ?? 0; //Dep Jere
        $prodconjunto_qrcode    = $info->qr_code ?? 0; // 2bead
        if ($iddep2 !== $prodconjunto_iddep) {
            return array('code' => -1, 'status' => 'fail', 'message' => 'Error: El nuevo componente tiene que estar en el mismo deposito realice la remision del repuesto al deposito ' . $prodconjunto_depnombre, 'data' => '');
        }

        $sql  = "SELECT *  FROM `app_componentes` compo left join app_productos p on p.id = compo.idprodconjunto_compoapp left join productos pp on pp.`id_producto` = p.idproducto left join stock_qrcode qr on qr.id_qrcode = p.id_qr WHERE `idprod_compoapp` = '" . $idprodnew . "' ORDER BY `id_compoapp` DESC";
   


        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data        = $stmt->fetch(PDO::FETCH_OBJ);
        
        
        /*
        object(stdClass)#319 (48) {
  ["id_compoapp"]=>
  int(947)
  ["idcompo_compoapp"]=>
  int(0)
  ["idprod_compoapp"]=>
  int(5911)
  ["idprodconjunto_compoapp"]=>
  int(5911)
  ["id"]=>
  int(5911)
  ["id_qr"]=>
  int(118)
  ["idproducto"]=>
  int(5)
  ["idgrupo"]=>
  NULL
  ["idmaq"]=>
  string(2) "11"
  ["idgps"]=>
  int(5398)
  ["idfoto"]=>
  NULL
  ["iddeposito"]=>
  int(139)
  ["nombrefoto"]=>
  NULL
  ["descripcion"]=>
  string(40) "<div>​Harina de maiz de primera.</div>"
  ["estado_producto"]=>
  string(1) "0"
  ["app_fabricacion"]=>
  string(10) "2023-10-11"
  ["app_fechavencimiento"]=>
  string(10) "2023-10-31"
  ["app_lote"]=>
  string(1) "1"
  ["app_cantidad_saldo"]=>
  int(100)
  ["id_producto"]=>
  int(5)
  ["producto"]=>
  string(14) "Harina de maiz"
  ["id_proveedor"]=>
  int(2)
  ["codigo"]=>
  string(1) "4"
  ["precio"]=>
  string(8) "22000.00"
  ["precio_mayorista"]=>
  string(8) "11111.00"
  ["costo"]=>
  string(8) "11000.00"
  ["iva"]=>
  int(21)
  ["peso"]=>
  string(5) "0.000"
  ["id_estado_producto"]=>
  int(1)
  ["etiqueta"]=>
  string(14) "harina de maiz"
  ["fecha_carga"]=>
  string(19) "2023-10-11 00:46:23"
  ["id_usuario_carga"]=>
  int(1)
  ["cantidad_minima"]=>
  int(1111)
  ["medida"]=>
  string(0) ""
  ["id_empresa"]=>
  int(1)
  ["produccion"]=>
  int(0)
  ["productos"]=>
  string(0) ""
  ["caducidad"]=>
  int(31)
  ["link"]=>
  string(26) "aquivaellinkdemercadolibre"
  ["unidad_medida"]=>
  int(3)
  ["prod_requiere_mantenimiento"]=>
  string(1) "0"
  ["tipoagrupacion"]=>
  string(1) "0"
  ["id_qrcode"]=>
  int(118)
  ["qr_code"]=>
  string(5) "b3018"
  ["estado_qrcode"]=>
  string(1) "1"
  ["idserie"]=>
  int(21)
  ["idusu"]=>
  int(256)
  ["fecha_activacion"]=>
  string(19) "2023-10-11 17:12:40"
}
        */

        if ($stmt->rowCount() > 0) {
            $conj_nombre = $data->producto ?? 0;
            $conj_idmaq  = $data->idmaq ?? 0;
            $conj_qr     = $data->qr_code ?? 0;
            return array('code' => -1, 'status' => 'fail', 'message' => 'El componente esta asignado a ' . $conj_nombre . ' QR ' . $conj_qr . ' Id maq ' . $conj_idmaq . ' procure primero desagrupar el componente antes de asignarlo a otra maquina.', 'data' => '');
        }

        $insertar = "INSERT INTO `app_componentes` (`id_compoapp`, `idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES (NULL, '" . $idcompo . "', '" . $idprodnew . "', '" . $idprodconjunto . "')";

        $stmt = $db->prepare($insertar);
        $stmt->execute();

        ServicioTecnico::cambiarEstado($db, $idprodnew, $idusu, '1', 'Componente asignado a la maquina  ' . $prodconjunto_qrcode);

        $sql  = "SELECT * FROM `app_componentes` WHERE `idprod_compoapp` = '" . $idprodnew . "' and idprodconjunto_compoapp = '" . $idprodconjunto . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return array('code' => 1, 'status' => 'success', 'message' => $prodnombrenew . ' asignado exitosamente', 'data' => '');
        } else {
            return array('code' => -1, 'status' => 'fail', 'message' => 'No se asigno el componente ' . $prodnombre, 'data' => '');
        }

    }

    public static function mklistconjunto(pdo $db)
    {
        $sql = "select p.id, pp.producto as prod_nombre, dep.dep_nombre, dep.dep_ciudad, componentes.id_compo from app_productos p left join productos pp on p.idproducto = pp.`id_producto`
        left join stock_depositos dep on dep.id_dep = p.iddeposito
        left join (SELECT compo.id_compo, compo.idprod_compo FROM `productos_conjunto` conj left join productos_cat cat on cat.idconjunto_cat = conj.id_conjunto left join productos_compo compo on compo.idcat_compo = cat.id_cat) as componentes on componentes.idprod_compo = p.idproducto
        WHERE pp.tipoagrupacion = '1' and p.id not in (SELECT idprodconjunto_compoapp FROM `app_componentes`)
        ORDER BY `dep`.`dep_ciudad` ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($data as $fila) {
                $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('" . $fila->id_compo . "', '" . $fila->id . "', '" . $fila->id . "'); ";
                $stmt     = $db->prepare($insertar);
                $stmt->execute();
            }
        }

        $sql = "select p.id, pp.producto as prod_nombre, dep.dep_nombre, dep.dep_ciudad, componentes.id_compo from app_productos p left join productos pp on p.idproducto = pp.`id_producto`
        left join stock_depositos dep on dep.id_dep = p.iddeposito
        left join (SELECT compo.id_compo, compo.idprod_compo FROM `productos_conjunto` conj left join productos_cat cat on cat.idconjunto_cat = conj.id_conjunto left join productos_compo compo on compo.idcat_compo = cat.id_cat) as componentes on componentes.idprod_compo = p.idproducto
        WHERE pp.tipoagrupacion = '1' and p.id not in (SELECT idprodconjunto_compoapp FROM `app_componentes`)
        ORDER BY `dep`.`dep_ciudad` ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }

    }

    public static function delcompobyid(pdo $db, $id_compoapp, $idusu)
    {

        $sql  = "SELECT idprod_compoapp FROM `app_componentes` where id_compoapp = '" . $id_compoapp . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info   = $stmt->fetch(PDO::FETCH_OBJ);
        $idprod = $info->idprod_compoapp ?? 0;

        ServicioTecnico::cambiarEstado($db, $idprod, $idusu, '0', 'Componente desagrupado');

        $sql  = "DELETE FROM `app_componentes` WHERE `id_compoapp` = '" . $id_compoapp . "' ";
        $stmt = $db->prepare($sql);
        $stmt->execute();


        $sql  = "SELECT idprod_compoapp FROM `app_componentes` where idprod_compoapp = '" . $idprod . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }
    // public static function addcompobyqr(pdo $db, $idprodconjunto, $idcompo, $qrcode)
    // {
    //     $sql = "SELECT iddeposito FROM app_productos p WHERE id = '" . $idprodconjunto . "'";
    //     $stmt = $db->prepare($sql);
    //     $stmt->execute();
    //     $data = $stmt->fetch(PDO::FETCH_OBJ);
    //     $iddeposito = $data->iddeposito;
    //     $sql = "SELECT p.id FROM app_productos p RIGHT JOIN productos pp ON pp.`id_producto` = p.idproducto RIGHT JOIN stock_qrcode qr ON qr.id_qrcode = p.id_qr
    //     LEFT JOIN productos_compo AS compo ON compo.idprod_compo = p.idproducto
    //     WHERE qr.estado_qrcode = '1'  AND p.iddeposito = '" . $iddeposito . "'  AND compo.id_compo = '" . $idcompo . "' AND qr_code = '" . $qrcode . "' ";
    //     $stmt = $db->prepare($sql);
    //     $stmt->execute();
    //     //var_dump($sql);
    //     if ($stmt->rowCount() > 0) {
    //         $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    //         $insertar = "INSERT INTO `app_componentes` (`idcompo_compoapp`, `idprod_compoapp`, `idprodconjunto_compoapp`) VALUES ('" . $idcompo . "', '" . $data[0]->id . "', '" . $idprodconjunto . "'); ";
    //         $stmt = $db->prepare($insertar);
    //         $stmt->execute();

    //         $sql = "UPDATE `app_productos` SET `estado_producto` = '1' WHERE `id` = '" . $data[0]->id . "'";
    //         $stmt = $db->prepare($sql);
    //         $stmt->execute();
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    public static function getprodbyqr(pdo $db, $qrcode)
    {
        /* Devolver idproducto=31&idconjunto=5&idcategoria=11&idpadrecat=7 */
        $sql = "SELECT pp.`id_producto` as idproducto, conjunto.id_conjunto as idconjunto, cat.id_cat as idcat, cat.idpadre_cat as idpadrecat, p.iddeposito, p.id as appprodid FROM app_productos p right join productos pp on pp.`id_producto` = p.idproducto right join stock_qrcode qr on qr.id_qrcode = p.id_qr left join productos_conjunto conjunto on conjunto.idprod_conjunto = p.idproducto right join productos_cat cat on cat.idprod_cat = p.idproducto
        where pp.tipoagrupacion = '1' and cat.idpadre_cat = '0' and cat.idconjunto_cat = conjunto.id_conjunto and p.idproducto = pp.`id_producto` and qr.qr_code = '$qrcode'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        return $data;
    }
    public static function getcatbyqr(pdo $db, $qrcode)
    {
        /* Devolver idproducto=31&idconjunto=5&idcategoria=11&idpadrecat=7 */
        $sql = "SELECT pp.`id_producto` as idproducto, conjunto.id_conjunto as idconjunto, cat.id_cat as idcat, cat.idpadre_cat as idpadrecat, p.iddeposito, p.id as appprodid FROM app_productos p right join productos pp on pp.`id_producto` = p.idproducto right join stock_qrcode qr on qr.id_qrcode = p.id_qr left join stock_productos_conjunto conjunto on conjunto.idprod_conjunto = p.idproducto right join stock_productos_cat cat on cat.idprod_cat = p.idproducto
        where pp.tipoagrupacion = '1' and cat.idpadre_cat = '0' and cat.idconjunto_cat = conjunto.id_conjunto and p.idproducto = pp.`id_producto` and qr.qr_code = '$qrcode'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        return $data;
    }
    public static function getprodcomponentes(pdo $db, $idcat, $idprodapp)
    {

        $sql = "SELECT

		id_compo,
		id_cat,
		id_compoapp,
		prod_nombre,
		prod_descrip,
		prod_foto,
		catnombre,
		IF(id_compoapp IS NOT NULL, id_compoapp, '0') AS id_compoapp,
		IF(qr_code IS NOT NULL, qr_code, '0') AS qr_code,
        IF(idprod_compoapp IS NOT NULL, idprod_compoapp, '0') AS idprod_compoapp



FROM (SELECT
		id_compo,
		cat.id_cat,
		idprod_compo,
		p.producto as prod_nombre,
		p.descripcion prod_descrip,
		'pic.jpg' as prod_foto,
		pp.producto AS catnombre,
		(SELECT id_compoapp FROM app_componentes WHERE idprodconjunto_compoapp = '$idprodapp' AND idcompo_compoapp = compo.`id_compo`) AS id_compoapp,

    (
    SELECT
        idprod_compoapp
    FROM
        app_componentes
    WHERE
        idprodconjunto_compoapp = '$idprodapp' AND idcompo_compoapp = compo.`id_compo`
) AS idprod_compoapp,
		(SELECT qr_code FROM app_componentes
	    LEFT JOIN app_productos p ON p.`id` = app_componentes.`idprod_compoapp`
	    LEFT JOIN stock_qrcode qr ON qr.`id_qrcode` = p.`id_qr`
	     WHERE idprodconjunto_compoapp = '$idprodapp' AND idcompo_compoapp = compo.`id_compo`) AS qr_code,
		`idconjunto_cat`	    FROM
		stock_productos_compo compo
		LEFT JOIN stock_productos_cat cat ON idcat_compo = cat.id_cat
		left join productos p on p.id_producto = compo.idprod_compo
		LEFT JOIN productos pp ON pp.`id_producto` = cat.idprod_cat
	    WHERE
		cat.id_cat = '$idcat') AS t1";
        //var_dump($sql);
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
}
