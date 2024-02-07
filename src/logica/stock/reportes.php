<?php
date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Reportes;
use \Firebase\JWT\JWT;
$app->group('/api/stock', function(\Slim\App $app) {
    //acceso al sistema por app
    $app->get('/getStock', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $data = Reportes::getStock($db);
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

    $app->get('/getMaquinas', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $data = Reportes::getMaquinas($db);
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