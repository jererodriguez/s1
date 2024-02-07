<?php
namespace Clases\Stock;
use \PDO;

class Permisos {
    public static function getPermisos(pdo $db): array
    {

        // $sql = "SELECT dep.id_dep as id, dep_nombre as nombre, dep_ciudad as ciudad, CONCAT(u.`nombre_usu`,' ',u.`apellido_usu`) AS agente, id_usu FROM ".DB_BASE.".stock_depositos dep LEFT JOIN ".DB_BASE.".stock_usuarios u ON dep.`dep_idusu` = u.id_usu";
        // $stmt = $db->query($sql);
        // $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        // return $data;
    }
}