<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Recetas;

$app->group('/api/stock/valeria', function (\Slim\App $app) {
    $app->get('/getRecetas', function (Request $request, Response $response, $args) {
        try {
            // Obtener objeto de la base de datos
            $db = $this->db;

            // Obtener datos de las recetas
            $data = Recetas::getRecetas($db);
            $db = null;

            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $data,
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'Error en los datos.',
                    'data' => [],
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
});
