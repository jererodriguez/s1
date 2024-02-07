<?php
date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Depositos;
use \Firebase\JWT\JWT;
$app->group('/api/stock', function(\Slim\App $app) {
    //acceso al sistema por app
    $app->get('/getDepositos', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
        try{
            // Get DB Object
            $db = $this->db;
            $data = Depositos::getDeposito($db);
            $db = null;
            return $this->response->withJson([
                'code' => 200,
                'status' => 'success', 
                'message' => 'Operacion exitosa',
                'data' => $data
            ]);

        } catch(PDOException $e){
            throw $e;
        }

    });
});