<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Caja;
use \Firebase\JWT\JWT;

$app->group('/api/stock/caja', function (\Slim\App $app) {

    $app->get('/getProductos', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
           // $id = $request->getParam('id');

            $data = Caja::getProductos($db);
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

    $app->get('/getClientes', function (Request $request, Response $response, $args) {
        $db = $this->db;
        $stmt = $db->query('SELECT id_cliente, razon_social, ruc FROM clientes');
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($clientes);
    });

    $app->get('/getMesas', function (Request $request, Response $response, $args) {
        $db = $this->db;
        $stmt = $db->query('SELECT id_mesa, area, nombre, estado FROM caja_mesas');
        $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($mesas);
    });

});
