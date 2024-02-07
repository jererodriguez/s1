<?php
namespace Clases\Stock;
use \PDO;
// print_r($settings);
class Depositos {
    private $log;
    function __construct($log =null) 
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
    public static function getDeposito(pdo $db): array
    {

        $sql = "SELECT dep.id_dep as id, dep_nombre as nombre, dep_ciudad as ciudad, CONCAT(u.`nombre_usu`,' ',u.`apellido_usu`) AS agente, id_usu FROM ".DB_BASE.".stock_depositos dep LEFT JOIN ".DB_BASE.".stock_usuarios u ON dep.`dep_idusu` = u.id_usu order by id_dep DESC";
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $data;
    }
}