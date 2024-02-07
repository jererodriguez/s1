<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set(TIMEZONE);
use \Clases\Stock\Productos;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
$app->group('/api/stock', function (\Slim\App$app) {

    $app->get('/getProductos', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $form = array();
            $request->getParam('tipo');
            switch ($request->getParam('tipo')) {
                case 'componentes':
                    $form['tipo'] = " and unidad_medida = '1'";
                    break;
                case '2':
                    $form['tipo'] = " and unidad_medida != '1' ";
                    break;
                default:
                    $form['tipo'] = " and tipoagrupacion like '%'";
                    break;
            }
            $data = Productos::getProductos($db, $form);
            $db = null;
            return $this->response->withJson([
                'code' => 200,
                'status' => 'success',
                'message' => 'Operacion exitosa',
                'data' => $data,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    });
    $app->get('/existeqr', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $qr = $request->getParam('qr');
            $data = Productos::existeQR($db, $qr);
            $db = null;
            $resultado = $data ? 1 : 0;
            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Esta en uso',
                    'data' => ["existeqr" => $resultado],
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'success',
                    'message' => 'No esta en uso',
                    'data' => ["existeqr" => $resultado],
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
    $app->post('/cargarProd', function (Request $request, Response $response, $args) {
        $id = $request->getParam('id');
        $qr = $request->getParam('qr');
        $idexterno = "0";
        $idexterno = $request->getParam('idexterno');
        $idDeposito = $request->getParam('idDeposito');
        $descripcion = $request->getParam('descripcion');
        $estado = $request->getParam('estado');
        $idusu = $request->getParam('idusu');
        $lat = $request->getParam('lat');
        $lon = $request->getParam('lon');
        $precision = $request->getParam('precision');
        $ubicacion_hora = $request->getParam('ubicacion_hora');
        try {
            $db = $this->db;
            $data = Productos::cargarProd($db, $id, $qr, $idDeposito, $descripcion, $lat, $lon, $precision, $ubicacion_hora, $estado, $idusu, $idexterno);
            $db = null;
            return $this->response->withJson([
                'code' => 200,
                'status' => 'success',
                'message' => 'Operacion exitosa',
                'data' => $data,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    });
    $app->post('/cargarImg', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        $claseProducto = new Productos();
        $fechaHora = date('YmdHis') . "_";

        try {
            // Get DB Object
            $db = $this->db;

            $status = $statusMsg = '';
            $uploadPath = "/var/www/dev/s1/public/img/stock/lg/productos/";
            var_dump($_FILES);
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
            $idproducto = $request->getParam('idProducto');
            $newObj = [
                "idproducto" => $idproducto,
                "nombrefoto" => $fileName,
            ];
            $res = $claseProducto->insertarImg($db, $newObj);
            if ($res) {
                $columnas = [];
                $data = Productos::getProductos($db, $columnas);
                $db = null;
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
    $app->get('/eliminarProd', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $qr = $request->getParam('qr');
            $data = Productos::eliminarProd($db, $qr);
            $db = null;
            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $data,
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
    $app->post('/insertCatalogo', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $form = array();
            $form['nombre'] = $request->getParam('nombre');
            $form['codint'] = $request->getParam('codint');
            $form['codfab'] = $request->getParam('codfab');
            $form['tienevencimiento'] = ($request->getParam('tienevencimiento')) ? '1' : '0';
            $form['mantenimiento'] = ($request->getParam('mantenimiento')) ? '1' : '0';
            $form['descripcion'] = $request->getParam('descripcion');
            $form['lat'] = $request->getParam('lat');
            $form['lon'] = $request->getParam('lon');
            $form['precision'] = $request->getParam('precision');
            $form['ubicacion_hora'] = $request->getParam('ubicacion_hora');
            $form['tipoagrupacion'] = $request->getParam('tipoagrupacion');
            $form['unidad_medida'] = $request->getParam('unidad_medida');
            $data = Productos::insertCatalogo($db, $form);
            $db = null;
            if ($data) {
                return $this->response->withJson([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Operacion exitosa',
                    'data' => $data,
                ]);
            } else {
                return $this->response->withJson([
                    'code' => 100,
                    'status' => 'fail',
                    'message' => 'No se guardo',
                    'data' => [],
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });
    $app->post('/insertCargarfoto', function (Request $request, Response $response, $args) {
        try {
            // Get DB Object
            $db = $this->db;
            $nombreProyecto = 'stock';
            $nombreModulo = 'productos';
            $idproducto = $request->getParam('idProducto');
            $img = $request->getParam('img');
            $nombre = $idproducto;
            
            $logger = $this->logger;
            $image = $idproducto.'.jpg';
            saveImage($img, $nombre, 'jpg');
        
            
            $newObj = [
                "id_estado_producto" => '1',
                "prod_foto" => $image,
            ];
            $claseProducto = new Productos();
            $res = $claseProducto->updateProducto($db, $newObj, $idproducto);
            if ($res) {
                $db = null;
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


    $app->post('/updateCatalogoweb', function (Request $request, Response $response, $args) {
        try {

            // return $this->response->withJson([
            //     'code' => 100,
            //     'status' => 'fail',
            //     'message' => 'No se pudo realizar la operacion',
            //     'data' => [$_POST, $_FILES],
            // ]);

            // Get DB Object
            $db = $this->db;
            $nombreProyecto = 'stock';
            $nombreModulo = 'productos';
            $prod_foto = $request->getParam('prod_foto');

            $responseImage = @uploadFiles('prod_foto', $nombreProyecto, $nombreModulo);
            // if (!isset($responseImage['error'])) {
            //     $newObj = [
            //         "prod_foto" => $prod_foto
            //     ];
            // }

            $prod_nombre = $request->getParam('prod_nombre');
            $prod_foto = $responseImage['name'][0];
            $prod_tipo = $request->getParam('prod_tipo');
            $prod_tienevencimiento = $request->getParam('prod_tienevencimiento');
            $mant_asistencia = $request->getParam('mant_asistencia');
            $mov_cod_fab = $request->getParam('mov_cod_fab');
            $mov_cod_int = $request->getParam('mov_cod_int');
            $prod_descrip = $request->getParam('prod_descrip');
            $idProducto = $request->getParam('idProducto');

            $newObj = [
                "producto as prod_nombre" => $prod_nombre,
                "id_estado_producto" => '1',
                "prod_foto" => $prod_foto,
                "tipoagrupacion" => $prod_tipo,
                "prod_tienevencimiento" => $prod_tienevencimiento,
                "mant_asistencia" => $mant_asistencia,
                "mov_cod_fab" => $mov_cod_fab,
                "mov_cod_int" => $mov_cod_int,
                "prod_descrip" => $prod_descrip,
                "idProducto" => $idProducto,
            ];

            $claseProducto = new Productos();
            $res = $claseProducto->updateCatalogoweb($db, $newObj);
            if ($res) {
                $db = null;
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

    $app->post('/insertCatalogoweb', function (Request $request, Response $response, $args) {
        try {

            // return $this->response->withJson([
            //     'code' => 100,
            //     'status' => 'fail',
            //     'message' => 'No se pudo realizar la operacion',
            //     'data' => [$_POST, $_FILES],
            // ]);

            // Get DB Object
            $db = $this->db;
            $nombreProyecto = 'stock';
            $nombreModulo = 'productos';
            $responseImage = @uploadFiles('prod_foto', $nombreProyecto, $nombreModulo);
            if (isset($responseImage['error'])) {
                throw new Exception($responseImage['error']);
            } else {
                $prod_nombre = $request->getParam('prod_nombre');
                $prod_foto = $responseImage['name'][0];
                $prod_tipo = $request->getParam('prod_tipo');
                $prod_tienevencimiento = $request->getParam('prod_tienevencimiento');
                $mant_asistencia = $request->getParam('mant_asistencia');
                $mov_cod_fab = $request->getParam('mov_cod_fab');
                $mov_cod_int = $request->getParam('mov_cod_int');
                $prod_descrip = $request->getParam('prod_descrip');
                $newObj = [
                    "producto as prod_nombre" => $prod_nombre,
                    "id_estado_producto" => '1',
                    "prod_foto" => $prod_foto,
                    "tipoagrupacion" => $prod_tipo,
                    "prod_tienevencimiento" => $prod_tienevencimiento,
                    "mant_asistencia" => $mant_asistencia,
                    "mov_cod_fab" => $mov_cod_fab,
                    "mov_cod_int" => $mov_cod_int,
                    "prod_descrip" => $prod_descrip,
                ];
                $claseProducto = new Productos();
                $res = $claseProducto->insertCatalogoweb($db, $newObj);
                if ($res) {
                    $db = null;
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
            }
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/etiquetarProdGranel', function (Request $request, Response $response, $args) {

        $form = array();

        $form['idusu'] = $request->getParam('idusu');
        $form['codqr'] = $request->getParam('codqr');
        $form['comentario'] = $request->getParam('comentario');
        $form['idproducto'] = $request->getParam('idproducto');
        $form['fechadefabricacion'] = $request->getParam('fechadefabricacion');
        $form['lote'] = $request->getParam('lote');
        $form['codfabricante'] = $request->getParam('codfabricante');
        $form['iddeposito'] = $request->getParam('iddeposito');
        $form['fechadevencimiento'] = $request->getParam('fechadevencimiento');
        $form['cantidad'] = $request->getParam('cantidad');

        $form['lat'] = $request->getParam('lat');
        $form['lon'] = $request->getParam('lon');
        $form['precision'] = $request->getParam('precision');
        $form['ubicacion_hora'] = $request->getParam('ubicacion_hora');
        try {
            $db = $this->db;
            $data = Productos::etiquetarProdGranel($db, $form);
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/fraccionarGranel', function (Request $request, Response $response, $args) {
        //  $data = $request->getParam('a');
        try {
            // Get DB Object
            $db = $this->db;
            $form = array();
            $form['qr'] = $request->getParam('qr');
            $data = Productos::fraccionarGranel($db, $form);
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/etiquetarFraccionagranel', function (Request $request, Response $response, $args) {

        $form = array();
        $form['idusu'] = $request->getParam('idusu');
        $form['codqr'] = $request->getParam('qr');
        $form['iddeposito'] = $request->getParam('iddeposito');
        $form['comentario'] = $request->getParam('comentario');
        $form['prodid'] = $request->getParam('prodid');
        $form['idprodconjunto'] = $request->getParam('idprodconjunto');
        $form['fechadefabricacion'] = $request->getParam('fechadefabricacion');
        $form['lote'] = $request->getParam('lote');
        $form['fechadevencimiento'] = $request->getParam('fechadevencimiento');
        $form['cantidad'] = $request->getParam('cantidad');
        $form['updatesaldo'] = $request->getParam('updatesaldo');
        $form['lat'] = $request->getParam('lat');
        $form['lon'] = $request->getParam('lon');
        $form['precision'] = $request->getParam('precision');
        $form['ubicacion_hora'] = $request->getParam('ubicacion_hora');
        try {
            $db = $this->db;
            $data = Productos::etiquetarFraccionaagranel($db, $form);
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

    $app->get('/etiquetarComponentes', function (Request $request, Response $response, $args) {

        $form = array();

        $form['idusu'] = $request->getParam('idusu');
        $form['codqr'] = $request->getParam('codqr');
        $form['comentario'] = $request->getParam('comentario');
        $form['idproducto'] = $request->getParam('idproducto');
        $form['fechadefabricacion'] = $request->getParam('fechadefabricacion');
        $form['lote'] = $request->getParam('lote');
        $form['codfabricante'] = $request->getParam('codfabricante');
        $form['iddeposito'] = $request->getParam('iddeposito');
        $form['fechadevencimiento'] = $request->getParam('fechadevencimiento');
        $form['cantidad'] = $request->getParam('cantidad');
        $form['estado'] = $request->getParam('estado');

        $form['lat'] = $request->getParam('lat');
        $form['lon'] = $request->getParam('lon');
        $form['precision'] = $request->getParam('precision');
        $form['ubicacion_hora'] = $request->getParam('ubicacion_hora');
        try {
            $db = $this->db;
            $estadoqr = Productos::estadoqr($db, $form['codqr']);
            if($estadoqr['code'] == 0) {
                $data = Productos::etiquetarComponentes($db, $form);
            } else {
                return $this->response->withJson($estadoqr);
            }
            $db = null;
            return $this->response->withJson($data);
        } catch (PDOException $e) {
            throw $e;
        }
    });

});
