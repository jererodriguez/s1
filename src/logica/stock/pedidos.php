<?php
date_default_timezone_set(TIMEZONE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Pedidos;
use \Firebase\JWT\JWT;

$app->get('/api/stock/getPedidos', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $form = array();


        $form['id_usuario'] = $request->getParam('id_usu');
        $form['pedido_estado'] = $request->getParam('pedido_estado');
        //var_dump($id_usuario);
        $data = Pedidos::getPedidos($db, $form);
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
}
);

$app->get('/api/stock/recepcionPedidos', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $id_destinatario = $request->getParam('id_usu');
        //var_dump($id_usuario);
        $data = Pedidos::recepcionPedidos($db, $id_destinatario);
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
}
);

$app->post('/api/stock/solicitarCompra', function (Request $request, Response $response, $args) {
    $form = array();
    $form['idnota'] = $request->getParam('idnota');
    $form['id_usu_remitente'] = $request->getParam('id_usu_remitente');
    $form['id_usu_destinatario'] = $request->getParam('id_usu_destinatario');
  
    try {
        $db = $this->db;
        $data = Pedidos::solicitarCompra($db, $form);
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
}
);


$app->post('/api/stock/crearNotadePedido', function (Request $request, Response $response, $args) {
    $form = array();
    $form['id_usu_remitente'] = $request->getParam('id_usu_remitente');
    $form['id_usu_destinatario'] = $request->getParam('id_usu_destinatario');
    $form['pedido_comentario'] = $request->getParam('comentario');
    $form['fechadevencimiento'] = $request->getParam('fechadevencimiento');
    try {
        $db = $this->db;
        $data = Pedidos::crearNotadePedido($db, $form);
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
}
);

$app->get('/api/stock/getNotaDetalles', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $idpedido = $request->getParam('idnota');
        //var_dump($id_usuario);
        $data = Pedidos::getNotaDetalles($db, $idpedido);
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
}
);

$app->get('/api/stock/nuevoItemPedido', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $idpedido = $request->getParam('idnota');
        $form = array();
        $form['idpedido'] = $request->getParam('idnota');
        $form['detalletxt'] = $request->getParam('detalletxt');
        $form['cantidad'] = $request->getParam('cantidad');

        //var_dump($id_usuario);
        $data = Pedidos::nuevoItemPedido($db, $form);
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
}
);

$app->get('/api/stock/delnotaPedido', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $idnota = $request->getParam('idnota');
        $estado_pedido = $request->getParam('estado_pedido');

        //var_dump($id_usuario);
        $data = Pedidos::delnotaPedido($db, $idnota, $estado_pedido);
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
}
);
$app->get('/api/stock/delDetallePedido', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $iddetalle = $request->getParam('iddetalle');

        //var_dump($id_usuario);
        $data = Pedidos::delDetallePedido($db, $iddetalle);
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
}
);

$app->get('/api/stock/editNotaDetalle', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $iddetalle = $request->getParam('iddetalle');
        $detalle_txt = $request->getParam('detalletxt');
        $cantidad = $request->getParam('cantidad');

        //var_dump($id_usuario);
        $data = Pedidos::editNotaDetalle($db, $iddetalle, $detalle_txt, $cantidad);
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
}
);


$app->get('/api/stock/recepcionNotaDetalles', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;

        $idpedido = $request->getParam('idnota');
        //var_dump($id_usuario);
        $data = Pedidos::recepcionNotaDetalles($db, $idpedido);
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
}
);

$app->get('/api/stock/rechazarPedido', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $form = array();
        $form['idnota'] = $request->getParam('idnota');
        $form['obs'] = $request->getParam('obs');
        $form['costototal'] = $request->getParam('costototal');

        //var_dump($id_usuario);
        $data = Pedidos::rechazarPedido($db, $form);


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
}
);

$app->get('/api/stock/gestionarPedido', function(Request $request, Response $response,$args){
    //  $data = $request->getParam('a');
    try{
        // Get DB Object
        $db = $this->db;
        $form = array();
        $form['idnota'] = $request->getParam('idnota');
        $form['pedido_estado'] = $request->getParam('pedido_estado');
        $form['obs'] = $request->getParam('obs');
        $form['costototal'] = $request->getParam('costototal');
        $form['fechadevencimiento'] = $request->getParam('fechadevencimiento');




        //var_dump($id_usuario);
        $data = Pedidos::gestionarPedido($db, $form);


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
}
);
