<?php
date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Agrupar;
use \Firebase\JWT\JWT;
$app->group('/api/stock', function(\Slim\App $app) {

    $app->post('/cargarGrupo', function (Request $request, Response $response, $args) {
        $idusu = $request->getParam('idusu'); 
        $tipoagrupacion = $request->getParam('tipoagrupacion'); 
        $idcatalogo = $request->getParam('idcatalogo'); 
        $idpersonalizado = $request->getParam('idpersonalizado'); 
        $iddeposito = $request->getParam('iddeposito'); 
        $idqr = $request->getParam('idqr'); 
        $descripcion = $request->getParam('descripcion'); 
        $lat = $request->getParam('lat'); 
        $lon = $request->getParam('lon'); 
        $precision = $request->getParam('precision'); 
        $ubicacion_hora = $request->getParam('ubicacion_hora'); 

        /*
        A partir de estos campos se crea el registro en app_productos
        */



        try {
            $db = $this->db;
            $data = Agrupar::agrupar_1($db, $idusu, $tipoagrupacion, $idcatalogo, $idpersonalizado, $iddeposito, $idqr, $descripcion, $lat, $lon, $precision, $ubicacion_hora);
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


    $app->get('/tipoMaquina', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $data = Agrupar::tipoMaquina($db);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });



    $app->post('/agrupar_1', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $idusu = $request->getParam('idusu');
            $tipoagrupacion = $request->getParam('tipoagrupacion');
            $idproducto = $request->getParam('idproducto');
            $idpersonalizado = $request->getParam('idpersonalizado');
            $iddeposito = $request->getParam('iddeposito');
            $idqr = $request->getParam('idqr');
            $descripcion = $request->getParam('descripcion');
            $lat = $request->getParam('lat');
            $lon = $request->getParam('lon');
            $precision = $request->getParam('precision');
            $ubicacion_hora = $request->getParam('ubicacion_hora');

    


            $data = Agrupar::agrupar_1($db,$idusu, $tipoagrupacion, $idproducto,$idpersonalizado,$iddeposito,$idqr, $descripcion,$lat,$lon,$precision,$ubicacion_hora);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => ["lastId" => $data]
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });

    $app->post('/agrupar_2', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $qr = $request->getParam('qr');
            $idAgrupar = $request->getParam('idAgrupar');

            $data = Agrupar::agrupar_2($db,$qr,$idAgrupar);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });


    $app->get('/getComponentes', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');

            $data = Agrupar::getComponentes($db,$id);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });

    $app->get('/eliminarComponente', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $id = $request->getParam('id');

            $data = Agrupar::eliminarComponente($db,$id);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });

    $app->post('/desagrupar', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $qr = $request->getParam('qr');

            $data = Agrupar::desagrupar($db,$qr);
            $db = null;
            if($data){
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success', 
                    'message' => 'Operacion exitosa',
                    'data' => $data
                ]);
            }else{
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail', 
                    'message' => 'No se guardo',
                    'data' => []
                ]);
            }

        } catch(PDOException $e){
            throw $e;
        }

    });

});