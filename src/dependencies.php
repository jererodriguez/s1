<?php
// DIC configuration
use Psr\Container\ContainerInterface as Container;
use Slim\Http\Request;
use Slim\Http\Response;
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// view renderer v2
$container['rendererV2'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    // Instantiate and add Slim specific extension
    $router = $c->get('router');

    // $twig = new \Twig\Environment($loader, [
    //     'debug' => true,
    //     // ...
    // ]);
    // $twig->addExtension(new \Twig\Extension\DebugExtension());


    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view = new \Slim\Views\Twig($settings['template_pathV2']);
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new \Twig\Extension\DebugExtension());


    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    // $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path']));
    return $logger;
};

// monolog
$container['loggerResponse'] = function ($c) {
    $settings = $c->get('settings')['loggerResponse'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    // $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path']));
    return $logger;
};

// PDO database library
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'].";charset=UTF8", $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

//Override the default Not Found Handler before creating App
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $data = array(
            "code" => 404,
            "status" => false,
            "message" => "Archivo no encontrado",
            "data" => []
        );
        return $response->withStatus(404)
            ->withJson( $data );  
    };
};

// Send Mail
$container['SendMail']  = function () {
    $myService = new SendMail();
    return $myService;
};

// Slim Framework application error handler / if it is set to false in setting
if($container->get('settings')['displayErrorDetails']){
    $container['errorHandler'] = function (Container $container) {
        $logger = $container->get('logger');
        return function(Request $request, Response $response, Throwable $exception) use ($logger) {

            $error = "Error number [".$exception->getCode()."] ".$exception->getMessage()." on line ".$exception->getLine()." in file ".$exception->getFile()."";
            $logger->error($error);

            if($exception->getCode() == 10 ){
                $error = $exception->getMessage();
            }

            return $response
                        // ->withStatus(500)
                        ->withHeader('Access-Control-Allow-Origin', '*')
                        ->withJson(
                            array(
                                'code' => 500,
                                "status"=> 'fail',
                                "message"=>"$error",
                                'data' => []
                            )
                        );  
            // return $response->withStatus(500) 
            //     ->withHeader('Content-Type', 'text/html')
            //     ->write('Algo salió mal!');
            
        };
    };
    
    $container['phpErrorHandler'] = function (Container $container) {
        return $container->get('errorHandler');
    };
}

