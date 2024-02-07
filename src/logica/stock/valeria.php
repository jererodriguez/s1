<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Valeria;
use \Firebase\JWT\JWT;

$app->group('/api/stock/valeria', function (\Slim\App $app) {



    
    $app->get('/getLocales', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
 
            //$idusu = "216";
            $data = Valeria::getLocales($db);
            $db = null;
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
                    'message' => 'Data error.',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    

    $app->get('/getproduccionremitida', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            if(!$idusu) {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se recibio el parametro idusu',
                    'data' => []
                ]);
            }
            //$idusu = "216";
            $data = Valeria::getProduccionRemitida($db, $idusu);
            $db = null;
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
                    'message' => 'Data error.',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    
    $app->get('/movRecepcionar', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            $qr = $request->getParam('qr');

            //$idusu = "216";
            $data = Movimiento::movRecepcionar($db, $idusu, $qr);
            $db = null;
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
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/movdelItem', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');

            $data = Valeria::movdelItem($db, $id);
            $db = null;
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
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });


    $app->get('/movaddItem', function (Request $request, Response $response, $args) {
        $id = $request->getParam('id');
        $qr = $request->getParam('qr');
        $idusu = $request->getParam('idusu');
        $comentario = $request->getParam('comentario');

        try {
            $db = $this->db;
            $data = Valeria::movaddItem($db, $qr, $id, $comentario);
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->post('/movstock', function (Request $request, Response $response, $args) {
        $idlocal = $request->getParam('idlocal');
        $idusu = $request->getParam('idusu');
        $obs = $request->getParam('descripcion');

        try {
            $db = $this->db;
            $data = Valeria::insertmov($db, $idlocal, $idusu, $obs);
            $db = null;
            return $this->response->withJson([
                'code' => 200,
                'status' => 'success',
                'message' => 'Operacion exitosa',
                'data' => $data
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    });



    $app->get('/getProduccion', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');

            $data = Valeria::getItemmov($db, $id);
            $db = null;
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
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    
    $app->get('/recibirmov', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');
            $comentario = $request->getParam('comentario');
            $lote = $request->getParam('lote');
            $idusu = $request->getParam('idusu');
            $idlocal = $request->getParam('idlocal');

            $data = Valeria::recibirmov($db, $id, $comentario, $lote, $idusu, $idlocal);
            $db = null;
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
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
});
