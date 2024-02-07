<?php 


$verificarToken = function (Request $request, Response $response, $next) {

    
    $token = $request->getQueryParam('token');

    if (empty($token)) {
        return $response->withJson(
            array('status' => 'fail', 'message' => 'Token no proporcionado.', 'data' => array())
        )->withStatus(401); // Devolver respuesta de error de autenticación
    }

    $settings = $this->get('settings'); // Obtener la configuración de la aplicación
    try {
        $decoded = JWT::decode($token, $settings['jwt']['secret'], array('HS256')); // Decodificar el token
        // Agregar los datos decodificados al objeto de solicitud para su uso posterior
        $request = $request->withAttribute('user', $decoded);
    } catch (\Exception $e) {
        return $response->withJson(
            array('status' => 'fail', 'message' => 'Token inválido.', 'data' => array())
        )->withStatus(401); // Devolver respuesta de error de autenticación
    }

    // Continuar con la ejecución de la ruta
    $response = $next($request, $response);
    return $response;
};
?>