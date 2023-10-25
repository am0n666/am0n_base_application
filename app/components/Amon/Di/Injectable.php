<?php

namespace Amon\Di;

use Amon\Di\Di;

abstract class Injectable implements InjectionAwareInterface
{
	protected $container;

	public function __get($propertyName)
	{
		$container = $this->getDI();
		if ($propertyName == "di") {
			$__t_s_1 = "di";
			$this->$__t_s_1 = $container;
			return $container;
		}
		if ($container->has($propertyName)) {
			$service = $container->getShared($propertyName);
			$this->$propertyName = $service;
			return $service;
		}
		trigger_error("Access to undefined property ".$propertyName);
		return  null ;
	}

	public function __isset($name)
	{
		return $this->getDI()->has($name);
	}

	public function getDI()
	{
		$container = $this->container;
		if (gettype($container) != "object") {
			$container = Di::getDefault();
			if (gettype($container) != "object") {
				throw (new Exception(Exception::containerServiceNotFound("internal services")));
			}
		}
		return $container;
	}

	public function setDI($container)
	{
		$this->container = $container;
	}
}
