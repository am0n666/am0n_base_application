<?php

namespace Amon;

use Closure;
use Amon\Application\AbstractApplication;
use Amon\Di\DiInterface;
use Amon\Application\Exception\AssetsFileNotFound;
use Amon\Helper\Other;
use Amon\Helper\Str;

class Application extends AbstractApplication
{
	private string $environment;
	protected $config;
	protected $dirs;
    public $router;
    public $default_route_url;

    public function __construct()
    {
		$this->config = $this->di->getConfig();
		$this->dirs = $this->di->getLoader()->getDirs(true);
        $this->environment = $this->config->application->environment;
		$this->router = $this->di->getRouter();

		$this->boot();
    }

    private function boot(): void
    {
        \error_reporting(0);
        if ($this->environment === 'dev') {
            \error_reporting(E_ALL);
            \ini_set('display_errors', '1');
        }

        date_default_timezone_set($this->config->application->timezone);

		$default_route = $this->router->getRoute($this->config->application->default_route);
		if (!$default_route) {
			throw new \Exception("A default route named <b>" . $this->config->application->default_route . "</b> does not exist");
		}
		$this->default_route_url = $this->router->getBasePath() . $default_route['url'];
		$this->view->addGlobal('default_path', $this->router->getBasePath() . $default_route['url']);
		$this->view->addGlobal('base_uri', $this->router->getBasePath());
    }

	public function run()
	{
		$this->router->handle();
	}
}	
