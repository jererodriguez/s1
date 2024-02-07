<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Clases\Stock\Appcomponentes;

$app->group('/api/stock', function (\Slim\App $app) {


    $app->get('/mklistconjunto', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
   

            $data = Appcomponentes::mklistconjunto($db);

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


    $app->get('/delcompobyid', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $id_compoapp = $request->getParam('id_compoapp');

            $idusu = $request->getParam('idusu');

            $data = Appcomponentes::delcompobyid($db,  $id_compoapp, $idusu);

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

    $app->get('/addcompobyqr', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idprodconjunto = $request->getParam('idprodconjunto');
            $idcompo = $request->getParam('idcompo');
            $qrcode = $request->getParam('qr');
            $idusu = $request->getParam('idusu');

            $data = Appcomponentes::addcompobyqr($db,  $idprodconjunto, $idcompo, $qrcode, $idusu);

            if ($data) {
                return $this->response->withJson($data);
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

    $app->get('/getcatbyqr', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $qrcode = $request->getParam('qrcode');

            $data = Appcomponentes::getcatbyqr($db, $qrcode);

            if ($data) {
                $url = '?idproducto='.$data->idproducto.'&idconjunto='.$data->idconjunto.'&idcategoria='.$data->idcat.'&idpadrecat='.$data->idpadrecat.'&iddeposito='.$data->iddeposito.'&appprodid='.$data->appprodid;

                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $url
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


    $app->get('/getprodcomponentes', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcat = $request->getParam('idcat');

            $idprodapp = $request->getParam('idprodapp');


            $data = Appcomponentes::getprodcomponentes($db, $idcat, $idprodapp);

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

    
    // $app->get('/addcompobyqr', function (Request $request, Response $response, $args) {
    //     try {
    //         $db = $this->db;
    //         $stockidproducto = $request->getParam('stockidproducto');

    //         $idusu = $request->getParam('idusu');

    //         $qrcode = $request->getParam('qrcode');

        

    //         $data = Appcomponentes::addcompobyqr($db, $stockidproducto, $idusu, $qrcode);

    //         if ($data) {

    //             return $this->response->withJson([
    //                 'code' => 200,
    //                 'status' => 'success',
    //                 'message' => 'Operacion exitosa',
    //                 'data' => $data
    //             ]);
    //         } else {
    //             return $this->response->withJson([
    //                 'code' => 100,
    //                 'status' => 'fail',
    //                 'message' => 'No se pudo conectar a la base de datos.',
    //                 'data' => []
    //             ]);
    //         }
    //     } catch (PDOException $e) {
    //         throw $e;
    //     }
    // });

});
