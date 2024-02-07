<?php
date_default_timezone_set(TIMEZONE);

use Clases\Stock\ServicioTecnico;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api/stock/serviciotecnico', function (\Slim\App$app) {


        $app->get('/aperturadepuerta', function (Request $request, Response $response, $args) {
        try {
            //cambiarEstado(pdo $db, $idprod, $qrcode, $idusu, $estado, $comentario)
            $db = $this->db;

            $form = array();
            $form['idprod'] = $request->getParam('idprod');
            $form['idusu'] = $request->getParam('idusu');
            $form['entrada'] = $request->getParam('entrada');
            $form['salida'] = $request->getParam('salida');
            $form['fisico'] = $request->getParam('fisico');
            $form['motivo'] = $request->getParam('motivo');
            $form['lat'] = $request->getParam('lat');
            $form['lon'] = $request->getParam('lon');
            $form['precision'] = $request->getParam('precision');
            $form['gpshora'] = $request->getParam('gpshora');
            $form['idprod'] = $request->getParam('idprod');
            $form['fecha'] = date('Y-m-d H:i:s');

            $data = ServicioTecnico::aperturadepuerta($db, $form);
            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/sustituir', function (Request $request, Response $response, $args) {
        try {
            //cambiarEstado(pdo $db, $idprod, $qrcode, $idusu, $estado, $comentario)
            $db = $this->db;
            $idprod = $request->getParam('idprod');
            $idprodconjunto = $request->getParam('idprodconjunto');
            $qrcode = $request->getParam('qrcode');
            $oldqr = $request->getParam('oldqr');
            $idusu = $request->getParam('idusu');

            $estado = '0';
            $comentario = 'Componente QR '.$oldqr.' reemplazado por el QR '.$qrcode;

            $data = ServicioTecnico::sustituir($db, $idprod, $qrcode, $idprodconjunto, $idusu);
            //var_dump($data);
            if ($data['code'] == '1') {

            $data_cambiarestado = ServicioTecnico::cambiarEstado($db, $idprod, $idusu, $estado, $comentario);
            } else {
                return $this->response->withJson($data);
            }
            if ($data_cambiarestado and $data['code'] == '1') {
                $data_getregistros = ServicioTecnico::getRegistros($db, $qrcode, $idusu);
                return $this->response->withJson($data_getregistros);
            } else {
                return $this->response->withJson(array('code' => -1, 'status' => 'fail', 'message' => 'No se completo la operacion de cambio de estado', 'data' => ''));
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });


    $app->get('/getRegistros', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $qrcode = $request->getParam('qrcode');

            $idusu = $request->getParam('idusu');

            $data = ServicioTecnico::getRegistros($db, $qrcode, $idusu);

            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/cambiarestado', function (Request $request, Response $response, $args) {
        try {
            //cambiarEstado(pdo $db, $idprod, $qrcode, $idusu, $estado, $comentario)
            $db = $this->db;
            $idprod = $request->getParam('idprod');

            $qrcode = $request->getParam('qrcode');

            $idusu = $request->getParam('idusu');
            $estado = $request->getParam('estado');
            $comentario = $request->getParam('comentario');

            $data = ServicioTecnico::cambiarEstado($db, $idprod, $idusu, $estado, $comentario);

            if ($data) {
                $data = ServicioTecnico::getRegistros($db, $qrcode, $idusu);
                return $this->response->withJson($data);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/desagrupar', function (Request $request, Response $response, $args) {
        try {
            //cambiarEstado(pdo $db, $idprod, $qrcode, $idusu, $estado, $comentario)
            $db = $this->db;
            $idprod = $request->getParam('idprod');

            $qrcode = $request->getParam('qrcode');

            $idusu = $request->getParam('idusu');
            $estado = $request->getParam('estado');
            $comentario = $request->getParam('comentario');
            $data = ServicioTecnico::desagrupar($db, $idprod);
            if ($data) {
                $data = ServicioTecnico::cambiarEstado($db, $idprod, $idusu, $estado, $comentario);

                if ($data) {
                    $data = ServicioTecnico::getRegistros($db, $qrcode, $idusu);
                    return $this->response->withJson($data);
                }
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

});
