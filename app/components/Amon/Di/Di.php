<?php

namespace Amon\Di;

use Amon\Di\Service;
use Amon\Di\DiInterface;
use Amon\Di\Exception\Exception;
use Amon\Di\Exception\ServiceResolutionException;
use Amon\Di\ServiceInterface;
use Amon\Events\ManagerInterface;
use Amon\Di\InjectionAwareInterface;
use Amon\Di\ServiceProviderInterface;

class Di implements DiInterface
{
	protected static $_default;
	protected $eventsManager;
	protected $services = [];
	protected $sharedInstances = [];

	public function __call($method, $arguments = [])
	{
		if (\Amon\Helper\Str::startsWith($method, "get")) {
			$possibleService = lcfirst(substr($method, 3));
			if (array_key_exists($possibleService, $this->services)) {
				$instance = $this->get($possibleService, $arguments);
				return $instance;
			}
		}
		if (\Amon\Helper\Str::startsWith($method, "set")) {
			if (\Amon\Helper\Other::fetchArray($definition, $arguments, 0)) {
				$this->set(lcfirst(substr($method, 3)), $definition);
				return	null ;
			}
		}
		throw (new \Exception("Call to undefined method or service '".$method."'"));
	}

	public function __construct()
	{
		if (!self::$_default) {
			self::$_default = $this;
		}
	}

	public function attempt($name, $definition, $shared = false)
	{
		if (array_key_exists($name, $this->services)) {
			return	false ;
		}
		$this->services[$name] = (new Service($definition, $shared));
		return $this->services[$name];
	}

	public function get($name, $parameters = null)
	{
		$instance = null ;
		if (\Amon\Helper\Other::fetchArray($service, $this->services, $name)) {
			$isShared = $service->isShared();
			if ($isShared&&array_key_exists($name, $this->sharedInstances)) {
				return $this->sharedInstances[$name];
			}
		}
		$eventsManager = $this->eventsManager;
		if (gettype($eventsManager) == "object") {
			$instance = $eventsManager->fire("di:beforeServiceResolve", $this, [
			"name" => $name,"parameters" => $parameters]);
		}
		if (gettype($instance) != "object") {
			if ($service !== null) {
				try {
					$instance = $service->resolve($parameters, $this);
				} catch (ServiceResolutionException $__t_v_1) {
					throw (new \Exception("Service '".$name."' cannot be resolved"));
				} if ($isShared) {
					$this->sharedInstances[$name] = $instance;
				}
			} else {
				if (!class_exists($name)) {
					throw (new \Exception("Service '".$name."' wasn't found in the dependency injection container"));
				}
				if (gettype($parameters) == "array" && count($parameters)) {
					$instance = \Amon\Helper\Other::createInstanceParams($name, $parameters);
				} else {
					$instance = \Amon\Helper\Other::createInstance($name);
				}
			}
		}
		if (gettype($instance) == "object") {
			if ($instance instanceof InjectionAwareInterface) {
				$instance->setDI($this);
			}
		}
		if (gettype($eventsManager) == "object") {
			$eventsManager->fire("di:afterServiceResolve", $this, [
			"name" => $name,"parameters" => $parameters,"instance" => $instance]);
		}
		return $instance;
	}

	public static function getDefault()
	{
		return self::$_default;
	}

	public function getInternalEventsManager()
	{
		return $this->eventsManager;
	}

	public function getRaw($name)
	{
		if (!\Amon\Helper\Other::fetchArray($service, $this->services, $name)) {
			throw (new Exception("Service '".$name."' wasn't found in the dependency injection container"));
		}
		return $service->getDefinition();
	}

	public function getService($name)
	{
		if (!\Amon\Helper\Other::fetchArray($service, $this->services, $name)) {
			throw (new Exception("Service '".$name."' wasn't found in the dependency injection container"));
		}
		return $service;
	}

	public function getServices()
	{
		return $this->services;
	}

	public function getShared($name, $parameters = null)
	{
		if (!\Amon\Helper\Other::fetchArray($instance, $this->sharedInstances, $name)) {
			$instance = $this->get($name, $parameters);
			$this->sharedInstances[$name] = $instance;
		}
		return $instance;
	}

	public function has($name)
	{
		return array_key_exists($name, $this->services);
	}

	public function offsetExists($name)
	{
		return $this->has($name);
	}

	public function offsetGet($name)
	{
		return $this->getShared($name);
	}

	public function offsetSet($name, $definition)
	{
		$this->setShared($name, $definition);
	}

	public function offsetUnset($name)
	{
		$this->remove($name);
	}

	public function register($provider)
	{
		$provider->register($this);
	}

	public function remove($name)
	{
		$services = $this->services;
		unset($services[$name]);
		$this->services = $services;
		$sharedInstances = $this->sharedInstances;
		unset($sharedInstances[$name]);
		$this->sharedInstances = $sharedInstances;
	}

	public static function reset()
	{
		self::$_default = null ;
	}

	public function set($name, $definition, $shared = false)
	{
		$this->services[$name] = (new Service($definition, $shared));
		return $this->services[$name];
	}

	public static function setDefault($container)
	{
		self::$_default = $container;
	}

	public function setInternalEventsManager($eventsManager)
	{
		$this->eventsManager = $eventsManager;
	}

	public function setService($name, $rawDefinition)
	{
		$this->services[$name] = $rawDefinition;
		return $rawDefinition;
	}

	public function setShared($name, $definition)
	{
		return $this->set($name, $definition, true);
	}
}
