<?php
date_default_timezone_set(TIMEZONE);

use Clases\Stock\Lectorqr;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api/stock', function (\Slim\App$app) {

    $app->get('/lectorqr1', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $qrcode = $request->getParam('qrcode');

            $idusu = $request->getParam('idusu');

            $data = Lectorqr::lectorqr1($db, $qrcode, $idusu);

            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    });

});
