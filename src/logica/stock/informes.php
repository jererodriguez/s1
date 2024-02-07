<?php
date_default_timezone_set(TIMEZONE);
use \Clases\Stock\Informes;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
$app->group('/api/stock/informes', function (\Slim\App$app) {


    $app->get('/getcantmaqxestado', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;

            $idusu = $request->getParam('idusu');

            $fecha1 = $request->getParam('fecha1');
            $fecha2 = $request->getParam('fecha2');

            $data = Informes::getCantMaqXEstado($db, $fecha1, $fecha2, $idusu);

            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/putcantmaqxtipoyestado', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db   = $this->db;
            $data = Informes::putCantxtipoyestado($db);
            $db   = null;
            if ($data) {
                return $this->response->withJson($data);
            } else {
                return $this->response->withJson([
                    'code'    => 100,
                    'status'  => 'fail',
                    'message' => 'No se pudo conectar a la base de datos',
                    'data'    => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

    $app->get('/putcantmaqxagente', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db   = $this->db;
            $data = Informes::putCantxagente($db);
            $db   = null;
            if ($data) {
                return $this->response->withJson($data);
            } else {
                return $this->response->withJson([
                    'code'    => 100,
                    'status'  => 'fail',
                    'message' => 'No se pudo conectar a la base de datos',
                    'data'    => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

});
