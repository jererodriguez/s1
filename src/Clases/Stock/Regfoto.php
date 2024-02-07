<?php

namespace Clases\Stock;

use \PDO;

class Regfoto
{
    private $log;

    public function __construct($log = null)
    {
        $this->log = $log;
    }

    public function cargarFoto($db, $obj = [])
    {
        if (empty($obj)) {
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

        if ($columnaAInsertar === 'idproducto,1_foto') {
            $sql = "INSERT INTO " . DB_BASE . ".app_productos ($columnaAInsertar) VALUES ($variableAInsertar)";
        } else {
            $sql = "UPDATE " . DB_BASE . ".app_productos SET 1_foto = :1_foto WHERE idproducto = :idproducto";
        }

        $stmt = $db->prepare($sql);

        foreach ($obj as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        try {
            $stmt->execute();
            // Si es una inserción, puedes obtener el último ID insertado.
            if ($columnaAInsertar === 'idproducto,1_foto') {
                $id = $db->lastInsertId();
                return $id;
            } else {
                return 2;
            }
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
}