<?php
date_default_timezone_set(TIMEZONE);

use Clases\Stock\AperturaPuerta;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/api/stock/aperturapuerta', function (\Slim\App$app) {

    $app->post('/cargarfoto', function (Request $request, Response $response, $args) {

        $fechaHora = date('YmdHis') . "_";

        try {
            // Get DB Object
            $db = $this->db;

            $status = $statusMsg = '';
            $uploadPath = "/var/www/dev/s1/public/img/aperturadepuertas/";

            if (!empty($_FILES["imgProducto"]["name"][0])) {
                // File info
                $fileName = $fechaHora . basename($_FILES["imgProducto"]["name"][0]);
                $imageUploadPath = $uploadPath . $fileName;
                $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION);

                // Allow certain file formats
                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Image temp source
                    $imageTemp = $_FILES["imgProducto"]["tmp_name"][0];
                    $imageSize = $_FILES["imgProducto"]["size"][0];

                    // Compress size and upload image
                    $compressedImage = compressImage($imageTemp, $imageUploadPath, 75);

                    if ($compressedImage) {
                        $compressedImageSize = filesize($compressedImage);

                        $status = 'success';
                        // $statusMsg = "Image compressed successfully.";
                    } else {
                        $statusMsg = "Image compress failed!";
                    }
                } else {
                    $statusMsg = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.';
                }
            } else {
                $statusMsg = 'Please select an image file to upload.';
            }
            $id = $request->getParam('id');
            $newObj = [
                "id" => $id,
                "nombrefoto" => $fileName,
            ];
            $res = AperturaPuerta::cargarfoto($db, $newObj);
            if ($res) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $res,
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se pudo realizar la operacion',
                    'data' => [],
                ]);
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

            $data = AperturaPuerta::getRegistros($db, $qrcode, $idusu);

            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    });

        $app->get('/insert', function (Request $request, Response $response, $args) {
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

            $data = AperturaPuerta::insert($db, $form);
            return $this->response->withJson($data);

        } catch (PDOException $e) {
            throw $e;
        }
    }); 




});
