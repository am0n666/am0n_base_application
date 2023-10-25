<?php
declare(strict_types=1);

use Amon\Helper\Other;
use Amon\View\Twig;
use Amon\Minifier\Minifier;

$di->setShared('loader', function () use ($loader) {
	return $loader;
});

$di->setShared('application', function ($di) {
	return new \Amon\Application($di);
});

$di->setShared('router', function () {
	$dirs = $this->getLoader()->getDirs(true);
	(is_file($dirs->configDir . "routes.php")) ? $routes = include_once $dirs->configDir . "routes.php" : $routes = [];
	$loader = $this->getLoader();
	$config = $this->getConfig();
	if (is_file($dirs->publicDir . 'webtools.config.php')) {
		include_once $dirs->publicDir . 'webtools.config.php';
		$loader->addBasePath(ATOOLSPATH . 'controllers/')->register();
		$webtools_route = [
			'name' => 'webtools',
			'url' => '/webtools',
			'methods' => 'GET|POST',
			'titles' => [
				'page' => 'Webtools',
				'navbar' => 'Webtools',
			],
			'active' => [
				'page' => 'webtools',
				'navbar' => 'webtools',
			],
		];
		$routes[] = $webtools_route;
	}
    return \Amon\Routing\Router::getInstance($routes);
});

$di->setShared('config', function () {
    return Other::toObject(include_once APP_PATH . "/config/config.php");
});

$di['view'] = function () {
	$dirs = $this->getLoader()->getDirs(true);
	$config = $this->getConfig();
	$viewsDirs[] = $dirs->viewsDir;
	if (is_file($dirs->publicDir . 'webtools.config.php')) {
		include_once $dirs->publicDir . 'webtools.config.php';
		$viewsDirs[] = ATOOLSPATH . 'views';
	}
    $view = new Twig(
		$viewsDirs, [
			'debug' => false,
			'cache' => false,
			'autoescape' => false
		]);
		$view->addExtension(new \Amon\TwigExtensions\Encore\EncoreBundle($dirs->jsonDir . "entrypoints.json"));
		$view->addExtension(new \Amon\TwigExtensions\publicDir());
		$view->addExtension(new \Amon\TwigExtensions\myDump());
    return $view;
};

$di->setShared('flash', function () {
	return new \Amon\Flash();
});
