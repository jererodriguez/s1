<?php
date_default_timezone_set(TIMEZONE);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Clases\Stock\Regfoto;
use \Firebase\JWT\JWT;
$app->group('/api/stock', function(\Slim\App $app) {


    $app->post('/cargarFoto', function(Request $request, Response $response,$args){
        //  $data = $request->getParam('a');
    
        try{
            // Get DB Object
            $db = $this->db;

            $nombreProyecto = 'stock';
            $nombreModulo = 'productos';
            $responseImage = @uploadFiles('imgProducto', $nombreProyecto, $nombreModulo);

            if( isset( $responseImage['error'] ) ){
                throw new Exception( $responseImage['error'] );
            }else{
                $hoy = getdate();
                $fecha = $hoy['year'].'-'.$hoy['mon'].'-'.$hoy['mday']." ".$hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];
                $idproducto = $request->getParam('idproducto');
                $iddep = $request->getParam('iddep');
                $image = $responseImage['name'][0];
                $nrofoto = $request->getParam('nrofoto');
                $idusu = $request->getParam('idusu');
                $newObj = [ 
                    "idprod_foto" => $idproducto,
                    "iddep_foto" => $iddep,
                    $nrofoto.'_foto' => $image,
                    'fecha_foto' => $fecha,
                    "idusu" => $idusu
                ];
            
                $claseProducto = new Regfoto();
                $res = $claseProducto->cargarFoto($db,$newObj);
            
                if($res){
                    $columnas = [
                    ];
                    $data = Regfoto::getProductos($db,$columnas);
                    $db = null;
                    return $this->response->withJson([
                        'code' => 200,
                        'status' => 'success', 
                        'message' => 'Operacion exitosa',
                        'data' => $res
                    ]);
                }else{
                    return $this->response->withJson([
                        'code' => 100,
                        'status' => 'fail', 
                        'message' => 'No se pudo realizar la operacion',
                        'data' => []
                    ]); 
                }
            }

        } catch(PDOException $e){
            throw $e;
        }

    });

    
});