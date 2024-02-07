<?php

namespace Clases\Stock;

use \PDO;    
    
class Recetas
{
    public static function getRecetas(PDO $db): array
    {
        $sql = "SELECT 
        recetas.id_receta,
        recetas.nombre,
        recetas.cantidad,
        recetas.unidad_receta,
        pasos.id_paso,
        pasos.recetaid,
        pasos.descripcion,
        pasos.imagen,
        pasos.video,
        pasos.minutos,
        pasos.segundos,
        ingredientes.id_ingrediente,
        ingredientes.idpaso,
        ingredientes.ingredienteid,
        ingredientes.cantidad_ingrediente,
        ingredientes.unidad_ingrediente,
        productos_ingredientes.producto as nombre_ingrediente,
        productos_ingredientes.descripcion as descripcion_ingrediente,
        herramientas.id_herramienta,
        herramientas.idpaso,
        herramientas.herramientaid,
        productos_herramientas.producto as nombre_herramienta,
        productos_herramientas.descripcion as descripcion_herramienta
    FROM val_recetas_cabecera recetas 
    LEFT JOIN val_recetas_pasos pasos ON pasos.recetaid = recetas.id_receta 
    LEFT JOIN val_recetas_ingredientes ingredientes ON ingredientes.idpaso = pasos.id_paso 
    LEFT JOIN val_recetas_herramientas herramientas ON herramientas.idpaso = pasos.id_paso 
    LEFT JOIN productos productos_ingredientes ON ingredientes.ingredienteid = productos_ingredientes.id_producto
    LEFT JOIN productos productos_herramientas ON herramientas.herramientaid = productos_herramientas.id_producto;
    ";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        $result = [];

        foreach ($data as $row) {
            $recetaIndex = array_search($row->id_receta, array_column($result, 'id_receta'));

            if ($recetaIndex === false) {
                $receta = [
                    'id_receta' => $row->id_receta,
                    'nombre' => $row->nombre,
                    'cantidad' => $row->cantidad,
                    'unidad' => $row->unidad_receta,
                    'pasos' => [],
                ];
                $result[] = $receta;
                $recetaIndex = count($result) - 1;
            }

            $pasoIndex = array_search($row->id_paso, array_column($result[$recetaIndex]['pasos'], 'id_paso'));

            if ($pasoIndex === false) {
                $paso = [
                    'id_paso' => $row->id_paso,
                    'descripcion' => $row->descripcion,
                    'imagen' => $row->imagen,
                    'video' => $row->video,
                    'minutos' => $row->minutos,
                    'segundos' => $row->segundos,
                    'ingredientes' => [],
                    'herramientas' => [],
                ];
                $result[$recetaIndex]['pasos'][] = $paso;
                $pasoIndex = count($result[$recetaIndex]['pasos']) - 1;
            }

            
    $ingrediente = [
        'id_ingrediente' => $row->id_ingrediente,
        'idpaso' => $row->idpaso,
        'idproducto' => $row->ingredienteid,
        'nombre' => $row->nombre_ingrediente,
        'descripcion' => $row->descripcion_ingrediente,
        'cantidad' => $row->cantidad_ingrediente,
        'unidad' => $row->unidad_ingrediente,
    ];

    $herramienta = [
        'id_herramienta' => $row->id_herramienta,
        'idpaso' => $row->idpaso,
        'idproducto' => $row->herramientaid,
        'nombre' => $row->nombre_herramienta,
        'descripcion' => $row->descripcion_herramienta,
    ];

    // $result[$recetaIndex]['pasos'][$pasoIndex]['ingredientes'][] = $ingrediente;
    // if (!in_array($herramienta, $result[$recetaIndex]['pasos'][$pasoIndex]['herramientas'])) {
    //     $result[$recetaIndex]['pasos'][$pasoIndex]['herramientas'][] = $herramienta;
    // }

            if (!in_array($herramienta, $result[$recetaIndex]['pasos'][$pasoIndex]['herramientas'])) {
                $result[$recetaIndex]['pasos'][$pasoIndex]['herramientas'][] = $herramienta;
            }

            $result[$recetaIndex]['pasos'][$pasoIndex]['ingredientes'][] = $ingrediente;
        }

        return $result;
    }
}
