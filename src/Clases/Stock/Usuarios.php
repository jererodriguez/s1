<?php

namespace Clases\Stock;

use \PDO;

// print_r($settings);

class usuarios
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

     * @param array $verComlumnas si se quiere obtener una columna

     * @return void

     */

    public static function listar(pdo $db, $verComlumnas = []): array

    {



        $columna = "*";

        if (count($verComlumnas)) {

            $columna = implode(",", $verComlumnas);
        }

        $sql = "SELECT $columna FROM " . DB_CON . ".stock_usuarios";

        $stmt = $db->query($sql);

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }

    /**

     * Undocumented function

     *

     * @param [type] $db conexion de base de datos

     * @param array $verComlumnas si se quiere obtener una columna

     * @return void

     */

    public static function listarBarrios(pdo $db, $verComlumnas = []): array

    {



        $columna = "*";

        if (count($verComlumnas)) {

            $columna = implode(",", $verComlumnas);
        }

        $sql = "SELECT $columna FROM " . DB_CON . ".barrios";

        $stmt = $db->query($sql);

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }

    /**

     * Undocumented function

     *

     * @param [type] $db conexion de base de datos

     * @param array $verComlumnas si se quiere obtener una columna

     * @return void

     */

    public static function listarCiudades(pdo $db, $verComlumnas = []): array

    {



        $columna = "*";

        if (count($verComlumnas)) {

            $columna = implode(",", $verComlumnas);
        }

        $sql = "SELECT $columna FROM " . DB_CON . ".ciudades";

        $stmt = $db->query($sql);

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }



    /**

     * Undocumented function

     *

     * @param [type] $db conexion de base de datos

     * @param [type] $ci filtar usuario por ci

     * @return void

     */

    public static function getDataByCI($db, $username)

    {

        $sqlCliente = "SELECT * FROM " . DB_BASE . ".usuarios WHERE nombre_usuario = '$username'";

        $stmt = $db->query($sqlCliente);

        $data = $stmt->fetch(PDO::FETCH_OBJ);



        $id = $data->id_usuario;

        $sql = "select us.rol, rm.id_menu, um.menu, um.submenu, um.url, um.orden, us.nombre_usuario
		from usuarios us
		inner join roles_menu rm on us.rol = rm.id_rol
		inner join menus um on um.id_menu = rm.id_menu where um.subsubmenu=0 and  um.estado = 1 and us.id_usuario = $id and um.menu like 'Stock'";

        $stmt = $db->query($sql);

        $data->permisos = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }



    public static function ver($db, $ver)

    {



        $sqlCliente = "SELECT ver_id, ver_ver,ver_fecha, ver_info FROM " . DB_BASE . ".app_ver ORDER BY ver_id DESC LIMIT 0,1";

        $stmt = $db->query($sqlCliente);

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        $ultver = $data->ver_ver;

        if ($ultver == $ver) {

            $acceso = 1;
        } else {

            $acceso = 0;
        }

        return $acceso;
    }



    /**

     * Undocumented function

     *

     * @param [type] $db conexion de base de datos

     * @param [type] $cuil filtar usuario por cuil

     * @return void

     */

    public static function getDataByCuil($db, $cuil)

    {

        $sql = "SELECT

                   *

                FROM " . DB_CON . ".stock_usuarios WHERE cuil_stock_usuarios = '$cuil'";

        $stmt = $db->query($sql);

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        return $data;
    }



    public function insertar($db, $obj = [])

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

            INSERT INTO " . DB_CON . ".stock_usuarios (

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

        } catch (\Throwable $th) {

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



    public function actualizar($db, $obj = [], $id_update)

    {

        if (!count($obj)) {

            return 0;
        }

        $columnaActualizar = "";

        $bandera = 0;

        $update = "";

        foreach ($obj as $key => $value) {

            if ($bandera == 0) {

                $bandera++;

                $columnaActualizar .= $key . " = :" . $key;

                $update .= "$key='$value'";
            } else {

                $columnaActualizar .= "," . $key . " = :" . $key;

                $update .= ",$key='$value'";
            }
        }



        $sql = "

            update " . DB_CON . ".stock_usuarios set

                $columnaActualizar

           where id_usuario = $id_update

        ";



        $stmt = $db->prepare($sql);

        foreach ($obj as $k => $v) {

            $stmt->bindValue(':' . $k, $v);
        }



        try {

            $stmt->execute();

            $id = $stmt->rowCount();

            return  1;
        } catch (\PDOException $th) {

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



    public function eliminar($db, $id_update)

    {

        $sql = "

            DELETE FROM " . DB_CON . ".stock_usuarios where id_usuario = $id_update

        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        $id = $db->lastInsertId();

        return $id;
    }



    /**

     * Undocumented function

     *

     * @param pdo $db

     * @param [type] $id_usuario

     * @param array $verComlumnas

     * @return array

     */

    public static function getDataById(pdo $db, $id_usuario, $verComlumnas = []): object

    {

        $columna = "*";

        if (count($verComlumnas)) {

            $columna = implode(",", $verComlumnas);
        }

        $sql = "SELECT $columna FROM " . DB_CON . ".stock_usuarios WHERE id_usuario = $id_usuario";

        $stmt = $db->query($sql);

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        return $data;
    }



    public static function getListaUsu(pdo $db, $id_usuario, $vercolumnas = []): array

    {

        $columna = "*";

        if (count($vercolumnas)) {

            $columna = implode(",", $vercolumnas);
        }

        $sql = "SELECT $columna FROM stock_usuarios WHERE id_usu != '" . $id_usuario . "' and estado_usu = '1'";

        $stmt = $db->query($sql);

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $data;
    }
}