<?php


require __DIR__ .'/../vendor/autoload.php';
// Set up tools
require __DIR__ . '/../src/tools.php';
// Set up config
require __DIR__ . '/../src/config.php';
$settings = require __DIR__ . '/../src/settings.php';
// Instantiate the app
// $app = new \Slim\App();
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
