<?php

namespace Amon\Routing;

use Amon\Routing\Url;

class Router
{
	static private $_instance;

	static private $url_helper;

	private $routes = array();

	private $standardController		=	"index";
	private $standardAction 		= 	"index";
	
	private $errorController		=	"error";
	private $notfoundErrorAction	=	"notfound";

	private $finalOutcome;

	public function __construct($routes)
	{
		$this->finalOutcome = new \ArrayObject();

		if (!empty($routes))
			$this->addRoutes($routes);
	}

	public static function getInstance($routes = [])
	{
		if ( !(self::$_instance instanceof self) )
		{
			self::$url_helper = new Url();
			self::$_instance = new self($routes);
		}
		return self::$_instance;
	}

	public function setController($name)
	{
		$this->finalOutcome->controller = $this->normalizeControllerName($name);
	}
	
	public function getController()
	{
		return $this->finalOutcome->controller;
	}

	public function setAction($name)
	{
		$this->finalOutcome->action = $this->normalizeActionName($name);
	}
	
	public function getAction()
	{
		return $this->finalOutcome->action;
	}

	public function getBasePath() {
		return self::$url_helper->getBasePath();
	}

	public function getRoutes() {
		return $this->routes;
	}

	public function getRoute($name) {
		if(isset($this->routes[$name])) {
			return $this->routes[$name];
		}
		return(false);
	}

	public function getRequestUri() {
		return str_replace(self::$url_helper->getBasePath(), '', self::$url_helper->getRequestUri());
	}

	public function redirect_to($url)
	{
		header('Location:'.$url);
		exit();
	}

	public function addRoutes($routes){
		if(!is_array($routes) && !$routes instanceof Traversable) {
			throw new \Exception('Routes should be an array or an instance of Traversable');
		}
		foreach($routes as $route) {
			$multi_methods = false !== strpos($route['methods'], '|');
			if ($multi_methods) {
				$methods = explode('|', $route['methods']);
			}else{
				$methods = [$route['methods']];
			}
			$this->map($route['url'], $methods, $route['active'], $route['titles'], $route['name']);
		}
	}

	protected function map($url, $methods, $active, $titles, $name) {
		$route['name'] = $name;
		$route['url'] = $url;
		$route['methods'] = $methods;
		$route['active'] = $active;
		$route['titles'] = $titles;

		if(!is_null($name)) {
			if(isset($this->routes[$name])) {
				throw new \Exception("Can not redeclare route '{$name}'");
			} else {
				$this->routes[$name] = $route;
			}
		}
		return;
	}

	public function checkUrl($url = '') {
		$result = array();
		$url_params = '';
		$url_method = '';

		if(!empty($url)) {
			if (strpos($url, '?')) {
				$uri = rtrim($this->_explode('?', $url)[0], '/') ?: '/';
			}else{
				if (!empty($this->_explode('/', $url))) {
					$uri = rtrim($this->_explode('/', $url, 1)[0], '/') ?: '/';
				}else{
					$uri = rtrim($url, '/') ?: '/';
				}
			}
			$urlValues = $this->_explode("/", $uri);
	
			if ( count($urlValues) === 1) {
				$urlValues[1] = $this->standardAction;
				$url_method = '/' . $urlValues[0] . "/" . $urlValues[1];
			}else if(count($urlValues) === 0) {
				$urlValues[0] = $this->standardController;
				$urlValues[1] = $this->standardAction;
				$url_method = '/' . $urlValues[0] . "/" . $urlValues[1];
			}else if ( count($urlValues) >= 2){
				$baseLength = strlen($urlValues[0] . "/" . $urlValues[1]);
				$url_params = substr($uri, $baseLength + 1);
				$url_method = str_replace($url_params, '', $uri);
			}
			(empty($url_method)) ?: $result['url_method'] = $url_method;
			(empty($url_params)) ?: $result['url_params'] = $url_params;
			$result['url_full'] = $url_method . $url_params;
		}
		return $result;
	}

	public function handle()
	{
		$params = [];
		$matches = $this->matches();
		if (empty($matches)) {
			return $this->callCustomControllerAction('error', 'notfound');
		}else{
			$url_params = $this->getArrayKey($matches, 'url_params');
	
			if ($url_params) {
				$params = ['url_params' => $matches['url_params']];
			}else{
				$params = [];
			}
			return $this->callCustomControllerAction($matches['controller'], $matches['action'], $params);
		}
	}

	public function callCustomControllerAction($controller_name, $action_name, $params = array()) {
		$this->setController($controller_name);
		$this->setAction($action_name);

		if (class_exists($this->getController())) {
			$controller = $this->getController();
			if ( method_exists($controller, $this->getAction()) ){
				return (new $controller())->{$this->getAction()}($params);
			}else{
				throw new \Exception('Action: ' . $this->getAction() . ' not found in ' . $this->getController() . '.');
			}
		}else{
			throw new \Exception('Class not found: ' . $this->getController() . '.');
		}
	}

	public function getMatches($match_method = 'url_full')
	{
		return $this->matches($match_method);
	}

	public function getPOST($var = null)
	{
        if (is_null($var)) {
            return(new \ArrayObject($_POST, 2));
        }
        if(isset($_POST)) {
            if(isset($_POST[$var])) {
                return(new \ArrayObject($_POST[$var], 2));
            }
        }
        return(null);
	}
	
	public function getGET($var = null)
	{
        if (is_null($var)) {
            return(new \ArrayObject($_GET, 2));
        }
        if(isset($_GET)) {
            if(isset($_GET[$var])) {
                return(new \ArrayObject($_GET[$var], 2));
            }
        }
        return(null);
	}

	public function _isAJAX()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$status = true;
		}else{
			$status = false;
		}
		return $status;
	}
	
	public function _isPost()
	{
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$status = true;
		}else{
			$status = false;
		}
		return $status;
	}
	
	public function _isGET()
	{
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			$status = true;
		}else{
			$status = false;
		}
		return $status;
	}


	private function matches($match_method = 'url_full') {
		$match_methods = ['url_full', 'url_method'];
		$correct_match_method = true === in_array($match_method, $match_methods);
		$is_full_url_method = true === ($match_method === 'url_full');

		$matches = [];

		if(empty($this->routes)) {
			throw new \Exception('No route has been defined.');
		}

		if(!$correct_match_method) {
			throw new \Exception('Incorrect value for match method: ' . $match_method . '. Possible values: url_full, url_method.');
		}

		foreach($this->routes as $route_name => $route) {
			$checked_route_url = $this->checkUrl($route['url']);
			$checked_request_url = $this->checkUrl($this->getRequestUri());

			if(preg_match("@^" . $checked_route_url[$match_method] . "$@D", $checked_request_url[$match_method])) {
				$correct_method = true === in_array($_SERVER['REQUEST_METHOD'], $route['methods']);
				if ($correct_method) {
					$matches['route_name'] = $route_name;
					$matches['url'] = $route['url'];
					$controller = $this->_explode('/', $checked_route_url['url_method'])[0];
					$action = $this->_explode('/', $checked_route_url['url_method'])[1];
					$matches['controller'] = $controller;
					$matches['action'] = $action;
					if (!$is_full_url_method) {
						$url_params = $this->getArrayKey($checked_request_url, 'url_params');
						if ($url_params) {
							$matches['url_params'] = $this->_explode('/', $url_params);
						}
					}
					$matches['titles'] = $route['titles'];
					$matches['active'] = $route['active'];
				}
			}
		}
		return $matches;
	}

	private function _explode($delimiter, $url, int $limit = null) {
		(isset($limit)) ? $array_url = explode($delimiter, $url, $limit) : $array_url = explode($delimiter, $url);
		($url !== '/') ?: $array_url = array();

		foreach($array_url as $key => $val){
			if (empty($val)) unset($array_url[$key]);
		}
		$array_url = array_values($array_url);
		return $array_url;
	}

	private function normalizeControllerName($name = '') {
		if (!empty($name))
			return ucfirst(strtolower($name)) . 'Controller';
		return(false);
	}

	private function normalizeActionName($name = '') {
		if (!empty($name))
			return ucfirst(strtolower($name)) . 'Action';
		return(false);
	}

	private function getArrayKey($array, $index)
	{
		if (isset($array) && isset($index)) {
			switch (gettype($index)) {
				case 'integer':
					if (isset($array[$index]))
						return $array[$index];
				case 'NULL':
					return false;
				case 'string':
					if (isset($array[$index]))
						return $array[$index];
				default:
					return false;
			}
		}
		return false;
	}
}
