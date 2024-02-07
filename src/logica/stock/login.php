<?php
date_default_timezone_set(TIMEZONE);

use \Clases\Stock\usuarios;
use \Firebase\JWT\JWT;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

$verificarToken = function (Request $request, Response $response, $next) {

    $token = $request->getHeaderLine('token');

    if (empty($token)) {
        return $response->withJson(
            array('status' => 'fail', 'message' => 'Token no proporcionado.', 'data' => array())
        )->withStatus(401); // Devolver respuesta de error de autenticación
    }

    $settings = $this->get('settings'); // Obtener la configuración de la aplicación
    try {
        $decoded = JWT::decode($token, $settings['jwt']['secret'], array('HS256')); // Decodificar el token

        // Verificar si el token ha expirado
        $currentTime = time();
        if ($decoded->exp < $currentTime) {
            return $response->withJson(
                array('status' => 'fail', 'message' => 'El token ha expirado.', 'data' => array())
            )->withStatus(401); // Devolver respuesta de error de autenticación
        }

        // Calcular la diferencia de tiempo en minutos
        $remainingTime = round(($decoded->exp - $currentTime) / 60);

        // Agregar los datos decodificados al objeto de solicitud para su uso posterior
        $request = $request->withAttribute('user', $decoded);
        $request = $request->withAttribute('remainingTime', $remainingTime);
    } catch (\Exception $e) {
        return $response->withJson(
            array('status' => 'fail', 'message' => 'Token inválido.', 'data' => array())
        )->withStatus(401); // Devolver respuesta de error de autenticación
    }

    // Continuar con la ejecución de la ruta
    $response = $next($request, $response);
    return $response;
};

$app->get('/api/stock/session', function (Request $request, Response $response, $args) {

    $remainingTime = $request->getAttribute('remainingTime');


    return $this->response->withJson([
        'code'    => 200,
        'status'  => 'success',
        'message' => 'Operacion exitosa',
        'data'    => [
            'remainingTime' => $remainingTime
        ],
    ]);

})->add($verificarToken);




 
$app->post('/api/stock/login', function (Request $request, Response $response) {
    $username    = $request->getParam('username');
    $password    = $request->getParam('password');
    $vertelefono = $request->getParam('version');

    $username = htmlentities(trim($username));
    try {
        // Get DB Object
        $db        = $this->db;
        $agente    = new usuarios();
        $verexiste = $agente->ver($db, $vertelefono); //compara la version del telefono con la ultima version en la bd
        $datos     = $agente->getDataByCI($db, $username);
        if ($verexiste == '0') {
            // 0 la version instalada en el telefono no es la ultima version de la app
            return $response->withJson(
                array('status' => 'fail', 'message' => 'Actualice su aplicación. ' . "> <a href='https://stock.quattropy.com/descargar/'>DESCARGAR</a> <", 'data' => array())
            );
        } elseif (!$datos) {
            return $response->withJson(
                array('status' => 'fail', 'message' => 'Usuario/Contraseña Incorrecta.', 'data' => array())
            );
        } else {
            if (md5($password) == @$datos->password) {
                $nombre   = $datos->nombre . " " . $datos->apellido;
                $id       = $datos->id_usuario;
                $permisos = $datos->permisos;
                $settings = $this->get('settings'); // get settings array.
                $option = [
                    'aud' => Aud(),
                    'id_usu' => $id,
                ];
                
                // Establecer la duración del token (24 horas en segundos)
                $expireTime = 24 * 60 * 60; // 24 horas en segundos
                
                // Calcular la fecha y hora de vencimiento
                $expirationTimestamp = time() + $expireTime;
                
                // Agregar el tiempo de expiración al payload
                $option['exp'] = $expirationTimestamp;
                
                $token = JWT::encode($option, $settings['jwt']['secret'], "HS256");

                return $response->withJson(
                    array(
                        'status'  => 'success',
                        'message' => 'Ok',
                        'data'    => array(
                            'user'     => $nombre,
                            'img'      => '',
                            'rol'      => '',
                            'id_usu'   => $id,
                            'sexo'     => '',
                            'token'    => $token,
                            'permisos' => $permisos,
                        ),
                    )
                );
            } else {
                return $response->withJson(
                    array('status' => 'fail', 'message' => 'Usuario/Contraseña Incorrecta .', 'data' => array())
                );
            }
        }

    } catch (PDOException $e) {
        throw $e;
    }
});


$app->get('/api/stock/getListaUsu', function (Request $request, Response $response, $args) {
    //  $data = $request->getParam('a');
    try {
        // Get DB Object
        $db         = $this->db;
        $id_usuario = $request->getParam('id_usu');

        //var_dump($id_usuario);
        $vercolumnas = array('id_usu', 'nombre_usu', 'apellido_usu', 'ci_usu');
        $data        = stock_usuarios::getListaUsu($db, $id_usuario, $vercolumnas);
        $db          = null;
        return $this->response->withJson([
            'code'    => 200,
            'status'  => 'success',
            'message' => 'Operacion exitosa',
            'data'    => $data,
        ]);

    } catch (PDOException $e) {
        throw $e;
    }

})->add($verificarToken);