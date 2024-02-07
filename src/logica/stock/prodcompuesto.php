<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Clases\Stock\Prodcompuesto;

$app->group('/api/stock', function (\Slim\App $app) {

    $app->get('/getruta', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcategoria = $request->getParam('idcategoria');

            $data = Prodcompuesto::getRuta($db, $idcategoria);
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

    $app->get('/crearCategoria', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcompo = $request->getParam('idcompo');

            $data = Prodcompuesto::crearCategoria($db, $idcompo);
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


    $app->get('/crearConjunto', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idproducto = $request->getParam('idproducto');

            $data = Prodcompuesto::crearConjunto($db, $idproducto);
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

    $app->get('/delcat', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcategoria = $request->getParam('idcategoria');

            $data = Prodcompuesto::delCat($db, $idcategoria);
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

    $app->get('/addcompo', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcategoria = $request->getParam('idcategoria');

            $idproducto = $request->getParam('idproducto');

            $data = Prodcompuesto::addCompo($db, $idcategoria, $idproducto);
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

    $app->get('/delcompo', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;

            $idcompo = $request->getParam('idcompo');

            $data = Prodcompuesto::delcompo($db, $idcompo);
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

    $app->get('/getConjuntos', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $data = Prodcompuesto::getConjuntos($db);
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

    $app->get('/getallcompo', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $data = Prodcompuesto::getallCompo($db);
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

    $app->get('/getsubcategorias', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idconjunto = $request->getParam('idconjunto');

            $idpadrecat = $request->getParam('idpadrecat');

            $idcat = $request->getParam('idcat');


            $data = Prodcompuesto::getsubCategorias($db, $idconjunto, $idpadrecat, $idcat);
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

    $app->get('/getcatcomponentes', function (Request $request, Response $response, $args) {
        try {
            $db = $this->db;
            $idcat = $request->getParam('idcat');

            $data = Prodcompuesto::getcatcomponentes($db, $idcat);
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

});
