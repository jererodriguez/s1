<?php
date_default_timezone_set(TIMEZONE);
use \Clases\Stock\Series;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
$app->group('/api/stock', function (\Slim\App$app) {

    $app->get('/serieProducto', function (Request $request, Response $response, $args) {
        $idserie = $request->getParam('idserie');
        try {
            // Get DB Object
            $db = $this->db;
            $columnas = [];
            $data = Series::serieProducto($db, $idserie);
            $db = null;
            return $this->response->withJson([
                'code' => 200,
                'status' => 'success',
                'message' => 'Operacion exitosa',
                'data' => $data,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/crearSerie', function (Request $request, Response $response, $args) {
        $form = array();
        $form['cantidad'] = $request->getParam('cantidad');
        $form['descripcion'] = $request->getParam('descripcion');

        $form['talle'] = $request->getParam('talle');
        $form['idusu'] = $request->getParam('idusu');
        $hoy = getdate();
        $form['fecha'] = $hoy['year'] . '-' . $hoy['mon'] . '-' . $hoy['mday'] . " " . $hoy['hours'] . ":" . $hoy['minutes'] . ":" . $hoy['seconds'];
        try {
            $db = $this->db;
            $data = Series::crearSerie($db, $form);
            $db = null;
            if ($data) {
                return $this->response->withJson($data);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

    $app->get('/getSeries', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $data = Series::getSeries($db);
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
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

    $app->get('/getQR', function (Request $request, Response $response, $args) {
        try {
            $idserie = $request->getParam('idserie');
            $db = $this->db;
            $data = Series::getQR($db, $idserie);
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
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

    $app->post('/agregarqr', function (Request $request, Response $response, $args) {
        try {
            $idserie = $request->getParam('idserie');
            $cant = intval($request->getParam('cant'));

            $db = $this->db;
            $data = Series::agregarqr($db, $idserie, $cant);
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
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }

        } catch (PDOException $e) {
            throw $e;
        }

    });

});
