<?php

namespace Clases\Stock;

use \PDO;


class Imagenes
{

    public static function compressImages($lg_folder, $sm_folder, $md_folder) {
        // Obtenemos la lista de archivos en la carpeta lg
        $lg_files = scandir($lg_folder);
        
        // Obtenemos la lista de archivos en la carpeta sm
        $sm_files = scandir($sm_folder);
        // Quitamos los elementos "." y ".." del array
        $sm_files = array_diff($sm_files, array(".", ".."));
        
        // Obtenemos la lista de archivos en la carpeta md
        $md_files = scandir($md_folder);
        // Quitamos los elementos "." y ".." del array
        $md_files = array_diff($md_files, array(".", ".."));
        
        // Comprimimos cada archivo en la carpeta lg
        foreach ($lg_files as $lg_file) {
            if ($lg_file != "." && $lg_file != "..") {
                $lg_filepath = $lg_folder . '/' . $lg_file;
                
                // Si la imagen no existe en la carpeta sm, la comprimimos y guardamos en la carpeta sm
                if (!in_array($lg_file, $sm_files)) {
                    $sm_filepath = $sm_folder . '/' . $lg_file;
                    compressImage($lg_filepath, $sm_filepath, 42, 42);
                }
                
                // Si la imagen no existe en la carpeta md, la comprimimos y guardamos en la carpeta md
                if (!in_array($lg_file, $md_files)) {
                    $md_filepath = $md_folder . '/' . $lg_file;
                    compressImage($lg_filepath, $md_filepath, 512, 512);
                }
            }
        }
    }
    
    // Función para comprimir una imagen
    public static function compressImage($source_path, $target_path, $maxWidth, $maxHeight) {
        $image = imagecreatefromstring(file_get_contents($source_path));
        $width = imagesx($image);
        $height = imagesy($image);
    
        $ratio = $width / $height;
    
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }
    
        $thumb = imagecreatetruecolor($maxWidth, $maxHeight);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);
        imagedestroy($image);
        
        imagejpeg($thumb, $target_path, 90);
    }
    
}
