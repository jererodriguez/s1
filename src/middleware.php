<?php
use Slim\Http\Request;
use Slim\Http\Response;

// Application middleware
$container = $app->getContainer();

// e.g: $app->add(new \Slim\Csrf\Guard); token 
    $app->add(new \Tuupola\Middleware\JwtAuthentication([
        "path" => MIDDLEWARE['path'],
        "ignore" => MIDDLEWARE['ignore'],

        
        //"logger" => $container["logger"],
        "attribute" => "token",
        "header" => "Token",
        "secure" => false,
        "relaxed" => ["localhost", "192.168.0.16"],
        "secret" => "123",
        "algorithm" => ["HS256"],
        "error" => function ($response, $arguments) {
            $data["status"] = false;
            $data["message"] = $arguments["message"];
            $data["response"] = 0;
            $data["error"] =  $arguments["message"];
            $data["data"] = ["msg" => "Acceso Denegado"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        },
        "callback" => function ($request, $response, $arguments) {
            return JWTAuth::verifyToken(str_replace('Bearer ', '', $request->getServerParams()['HTTP_AUTHORIZATION']));
        }
    ]));


//para probar algo
    $app->add(function ($req, $res, $next) {
        return $next($req, $res); 
    });

    


// Middleware to handle minor errors
$app->add(function (Request $request, Response $response, $next) {
    $logger = $this->logger;
    $settings = $this->get('settings');
    // error handler function
    $myHandlerForMinorErrors = function ($errno, $errstr, $errfile, $errline) use ($response, $logger,$settings) {
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
                $logger->error("Error number [$errno] $errstr on line $errline in file $errfile");
                break;
            case E_USER_WARNING:
            case E_WARNING:
                $logger->warning("Error number [$errno] $errstr on line $errline in file $errfile");
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
                $logger->notice("Error number [$errno] $errstr on line $errline in file $errfile");
                break;
            default:
                $logger->notice("Error number [$errno] $errstr on line $errline in file $errfile");
                break;
        }
        // Optional: Write error to response
        // $response->withJson(array("response" => "0", "error" => "Error: [$errno] $errstr<br>\n" ,"data" => array("msg" => "Error Interno")));
        // $response = $response->getBody()->write("Error: [$errno] $errstr<br>\n");
        // Don't execute PHP internal error handler
        if( $settings['displayErrorDetails'] ) return false;
        return true;
    };

    // Set custom php error handler for minor errors
    set_error_handler($myHandlerForMinorErrors, E_NOTICE | E_STRICT);
    return $next($request, $response);
});

//para probar algo
$app->add(function ($req, $res, $next) {
    ##### Ejemplo para enviar correo por el servidor
        // $title = '📌 Sistema';
        // $from = ['developer@proinso.sa.com' => 'Sistema de Gestión'];
        // $to = ['saldivarcristian@gmail.com', 'echinfer@gmail.com' => 'echin','comodin.taller.servicios@gmail.com','saldivarcristian@hotmail.com'];
        // $body = '.!.';
        // $this->SendMail->Send($title,$from,$to,$body);
    ##### Fin 

    // ServidorEmail::ValidacionFotoUsuario(date('Y-m-d H:i:s'), 3582196);

    return $next($req, $res); 
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept,Token, Origin, id, key, company, Authorization, Version,Game,rol')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// $app->add(new Tuupola\Middleware\CorsMiddleware([
//     "origin" => ["*"],
//     "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
//     "headers.allow" => ["Content-Type", "Accept", "Origin","Game", "Authorization", "version","id","key", "company","token"],
//     "headers.expose" => [],
//     "credentials" => false,
//     "cache" => 0,
// ]));
//para probar algo
$app->add(function ($req, $res, $next) {
    //Get resource URI
    $uri = $req->getUri()->getPath();

    $uri = preg_replace("#/+#", "/", $uri);
  
    /* If request path is matches ignore should not authenticate. */
    foreach ((array) MIDDLEWARE['ignore'] as $ignore) {
        $ignore = rtrim($ignore, "/");
        
        if (!!preg_match("@^{$ignore}(/.*)?$@", (string) $uri)) {
            $token = $req->getAttribute("token");
            if ($token['aud'] === Aud()) {
                return $next($req, $res); 
            }
            else{
                return $res->withJson([
                    'code' => 403,
                    'status' => 'error', 
                    'message' => 'Acceso Denegado',
                    'data' =>[]
                ]);            
            }     
        }
    }
    return $next($req, $res); 
});