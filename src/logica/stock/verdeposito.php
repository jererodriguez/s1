<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Clases\Stock\Verdeposito;

$app->group('/api/stock', function (\Slim\App $app) {
    $app->get('/verdeposito', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            $data = Verdeposito::verdeposito($db, $idusu);
            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se pudo conectar a la base de datos.',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/verdeposito_estado', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $qr = $request->getParam('qr');
            $idusu = $request->getParam('idusu');
            $newestado = $request->getParam('newestado');

            $data = Verdeposito::verdeposito_estado($db, $qr, $idusu,  $newestado);

            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se pudo conectar a la base de datos.',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
});
