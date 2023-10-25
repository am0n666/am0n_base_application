<?php

use Amon\Mvc\Controller;

class ControllerBase extends Controller
{
	public $config;
	public $flash;
	public $dirs;

	public function __construct() {
		$this->config = $this->di->getConfig();
		$this->flash = $this->di->getFlash();
		$this->dirs = $this->di->getLoader()->getDirs(true);
	}

	public function getTemplateFile() {
		$router = $this->di->getRouter();
		$controller = strtolower(str_replace('Controller', '', $router->getController()));
		$action = strtolower(str_replace('Action', '', $router->getAction()));
		return $controller . DIRECTORY_SEPARATOR . $action . '.html';
	}

    public function render($data = [])
    {
		$twig = $data;
		$matches = $this->router->getMatches();
		if (!empty($matches)) {
			$twig['current']['route_name'] = $matches['route_name'];
			$twig['current']['titles'] = $matches['titles'];
			$twig['current']['active'] = $matches['active'];
		}
		$twig['current']['titles']['application'] = $this->config->application->title;
		$twig['site_language'] = $this->config->application->language;
		$twig['svg_logo'] = print_svg($this->dirs->publicDir . 'img/logo.svg');
		$twig['routes'] = $this->router->getRoutes();
		return $this->view->render($this->getTemplateFile(), $twig);
    }
}

