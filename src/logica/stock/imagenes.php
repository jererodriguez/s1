<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;


$app->group('/api/stock', function (\Slim\App$app) {

    $app->get('/compress', function (Request $request, Response $response) {
        // Ruta de la carpeta que contiene las imágenes
     
        
        // Obtener la lista de imágenes en la carpeta /lg
$lgFiles = scandir('/var/www/stock_comodin/s1/public/img/stock/lg/productos');

// Obtener la lista de imágenes en la carpeta /md
$mdFiles = scandir('/var/www/stock_comodin/s1/public/img/stock/md/productos');

// Obtener la lista de imágenes en la carpeta /sm
$smFiles = scandir('/var/www/stock_comodin/s1/public/img/stock/sm/productos');

// Contadores de imágenes guardadas
$mdCount = 0;
$smCount = 0;

// Iterar a través de las imágenes en la carpeta /lg
foreach ($lgFiles as $lgFile) {
    // Comprobar si el archivo es una imagen (se puede agregar más extensiones si es necesario)
    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $lgFile)) {
        // Comprobar si el archivo no está en la carpeta /md
        if (!in_array($lgFile, $mdFiles)) {
            // Comprimir la imagen a 512x512 y guardarla en la carpeta /md
            $image = imagecreatefromstring(file_get_contents('./lg/' . $lgFile));
            $newImage = imagescale($image, 512, 512);
            imagedestroy($image);
            imagejpeg($newImage, './md/' . $lgFile, 80);
            imagedestroy($newImage);
            $mdCount++;
        }
        // Comprobar si el archivo no está en la carpeta /sm
        if (!in_array($lgFile, $smFiles)) {
            // Comprimir la imagen a 42x42 y guardarla en la carpeta /sm
            $image = imagecreatefromstring(file_get_contents('./lg/' . $lgFile));
            $newImage = imagescale($image, 42, 42);
            imagedestroy($image);
            imagejpeg($newImage, './sm/' . $lgFile, 80);
            imagedestroy($newImage);
            $smCount++;
        }
    }
}


        // Enviar respuesta de éxito
        $response->getBody()->write("Imágenes guardadas en /md: $mdCount\n"."Imágenes guardadas en /sm: $smCount\n");
        return $response->withStatus(200);
    });

});

