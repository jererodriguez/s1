<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Movimiento;
use \Firebase\JWT\JWT;

$app->group('/api/stock', function (\Slim\App $app) {

    $app->get('/comentaritem', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $iditem = $request->getParam('iditem');
            $comentario = $request->getParam('comentario');

            $data = Movimiento::comentarItem($db, $iditem, $comentario);
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

    $app->get('/getTraslados', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            //$idusu = "216";
            $data = Movimiento::getTraslados($db, $idusu);
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

    $app->get('/getRecepciones', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            //$idusu = "216";
            $data = Movimiento::getRecepciones($db, $idusu);
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

            $data = Movimiento::movdelItem($db, $id);
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

        try {
            $db = $this->db;
            $data = Movimiento::movaddItem($db, $qr, $id, $idusu);
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->post('/movstock', function (Request $request, Response $response, $args) {
        $iddeposito = $request->getParam('idDeposito');
        $idusu = $request->getParam('idusu');
        $obs = $request->getParam('descripcion');

        try {
            $db = $this->db;
            $data = Movimiento::insertmov($db, $iddeposito, $idusu, $obs);
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



    $app->get('/getMov', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');

            $data = Movimiento::getItemmov($db, $id);
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
            $idusu = $request->getParam('idusu');


            $data = Movimiento::recibirmov($db, $id, $idusu);
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
