<?php
/**
 * Application front controller
 */
require __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['app.rootdir'] = realpath(__DIR__ . '/..');

// Add app confs
$configuration = require $app['app.rootdir'] . '/app/config/config.php';
foreach($configuration as $configKey => $configValue) {
    $app[$configKey] = $configValue;
}

// Configure available application routes
$routes = require $app['app.rootdir'] . '/app/config/routes.php';
foreach($routes as $url => $action) {
	$app->get($url, [new $action[0]($app), $action[1]]);
}

$app->error([new Neveldo\DataPesticides\Controller\ErrorController($app), "errorAction"]);

$app->run();