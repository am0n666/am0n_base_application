<?php

namespace Amon\Di;

use Closure;
use Amon\Di\Exception\ServiceResolutionException;
use Amon\Di\Service\Builder;

class Service implements ServiceInterface
{
	protected $definition;
	protected $resolved = false ;
	protected $shared = false ;
	protected $sharedInstance;

	final public function __construct($definition, $shared = false)
	{
		$this->definition = $definition;
		$this->shared = $shared;
	}

	public function getDefinition()
	{
		return $this->definition;
	}

	public function getParameter($position)
	{
		$definition = $this->definition;
		if (gettype($definition) != "array") {
			throw (new Exception("Definition must be an array to obtain its parameters"));
		}
		if (\Amon\Helper\Other::fetchArray($arguments, $definition, "arguments")) {
			if (\Amon\Helper\Other::fetchArray($parameter, $arguments, $position)) {
				return $parameter;
			}
		}
		return  null ;
	}

	public function isResolved()
	{
		return $this->resolved;
	}

	public function isShared()
	{
		return $this->shared;
	}

	public function resolve($parameters = null, $container = null)
	{
		$shared = $this->shared;
		if ($shared) {
			$sharedInstance = $this->sharedInstance;
			if ($sharedInstance !== null) {
				return $sharedInstance;
			}
		}
		$found = true ;
		$instance = null ;
		$definition = $this->definition;
		if (gettype($definition) == "string") {
			if ($container !== null) {
				$instance = $container->get($definition, $parameters);
			} elseif (class_exists($definition)) {
				if (gettype($parameters) == "array"&&count($parameters)) {
					$instance = create_instance_params($definition, $parameters);
				} else {
					$instance = create_instance($definition);
				}
			} else {
				$found = false;
			}
		} else {
			if (gettype($definition) == "object") {
				if ($definition instanceof Closure) {
					if (gettype($container) == "object") {
						$definition = Closure::bind($definition, $container);
					}
					if (gettype($parameters) == "array") {
						$instance = call_user_func_array($definition, $parameters);
					} else {
						$instance = call_user_func($definition);
					}
				} else {
					$instance = $definition;
				}
			} else {
				if (gettype($definition) == "array") {
					$builder = (new Builder());
					$instance = $builder->build($container, $definition, $parameters);
				} else {
					$found = false ;
				}
			}
		}
		if ($found === false) {
			throw (new ServiceResolutionException());
		}
		if ($shared) {
			$this->sharedInstance = $instance;
		}
		$this->resolved = true ;
		return $instance;
	}

	public function setDefinition($definition)
	{
		$this->definition = $definition;
	}

	public function setParameter($position, $parameter)
	{
		$definition = $this->definition;
		if (gettype($definition) !== "array") {
			throw (new Exception("Definition must be an array to update its parameters"));
		}
		if (\Amon\Helper\Other::fetchArray($arguments, $definition, "arguments")) {
			$arguments[$position] = $parameter;
		} else {
			$arguments = [
				$position => $parameter
			];
		}
		$definition["arguments"] = $arguments;
		$this->definition = $definition;
		return $this;
	}

	public function setShared($shared)
	{
		$this->shared = $shared;
	}

	public function setSharedInstance($sharedInstance)
	{
		$this->sharedInstance = $sharedInstance;
	}
}
