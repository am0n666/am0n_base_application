<?php

namespace Amon\Di\Service;

use Amon\Di\DiInterface;
use Amon\Di\Exception;

class Builder
{
	public function build($container, $definition, $parameters = null)
	{
		if (!\Amon\Helper\Other::fetchArray($className, $definition, "className")) {
			throw (new Exception("Invalid service definition. Missing 'className' parameter"));
		}
		if (gettype($parameters) == "array") {
			if (count($parameters)) {
				$instance = create_instance_params($className, $parameters);
			} else {
				$instance = create_instance($className);
			}
		} else {
			if (\Amon\Helper\Other::fetchArray($arguments, $definition, "arguments")) {
				$instance = create_instance_params($className, $this->buildParameters($container, $arguments));
			} else {
				$instance = create_instance($className);
			}
		}
		if (\Amon\Helper\Other::fetchArray($paramCalls, $definition, "calls")) {
			if (gettype($instance) != "object") {
				throw (new Exception("The definition has setter injection parameters but the constructor didn't return an instance"));
			}
			if (gettype($paramCalls) != "array") {
				throw (new Exception("Setter injection parameters must be an array"));
			}
			foreach ($paramCalls as $methodPosition => $method) {
				if (gettype($method) != "array") {
					throw (new Exception("Method call must be an array on position ".$methodPosition));
				}
				if (!\Amon\Helper\Other::fetchArray($methodName, $method, "method")) {
					throw (new Exception("The method name is required on position ".$methodPosition));
				}
				$methodCall = [
				$instance,$methodName];
				if (\Amon\Helper\Other::fetchArray($arguments, $method, "arguments")) {
					if (gettype($arguments) != "array") {
						throw (new Exception("Call arguments must be an array ".$methodPosition));
					}
					if (count($arguments)) {
						call_user_func_array($methodCall, $this->buildParameters($container, $arguments));
						continue ;
					}
				}
				call_user_func($methodCall);
			}
		}
		if (\Amon\Helper\Other::fetchArray($paramCalls, $definition, "properties")) {
			if (gettype($instance) != "object") {
				throw (new Exception("The definition has properties injection parameters but the constructor didn't return an instance"));
			}
			if (gettype($paramCalls) != "array") {
				throw (new Exception("Setter injection parameters must be an array"));
			}
			foreach ($paramCalls as $propertyPosition => $property) {
				if (gettype($property) != "array") {
					throw (new Exception("Property must be an array on position ".$propertyPosition));
				}
				if (!\Amon\Helper\Other::fetchArray($propertyName, $property, "name")) {
					throw (new Exception("The property name is required on position ".$propertyPosition));
				}
				if (!\Amon\Helper\Other::fetchArray($propertyValue, $property, "value")) {
					throw (new Exception("The property value is required on position ".$propertyPosition));
				}
				$instance->$propertyName = $this->buildParameter($container, $propertyPosition, $propertyValue);
			}
		}
		return $instance;
	}
	/*
	**
		 * Resolves a constructor/call parameter
		 *
		 * @return mixed
		 *
	*/
	private function buildParameter($container, $position, $argument)
	{
		if (!\Amon\Helper\Other::fetchArray($type, $argument, "type")) {
			throw (new Exception("Argument at position ".$position." must have a type"));
		}
		switch ($type) {
			case "service":
				if (!\Amon\Helper\Other::fetchArray($name, $argument, "name")) {
					throw (new Exception("Service 'name' is required in parameter on position ".$position));
				}
				if (gettype($container) != "object") {
					throw (new Exception("The dependency injector container is not valid"));
				}
				return $container->get($name);
			case "parameter":
				if (!\Amon\Helper\Other::fetchArray($value, $argument, "value")) {
					throw (new Exception("Service 'value' is required in parameter on position ".$position));
				}
				return $value;
			case "instance":
				if (!\Amon\Helper\Other::fetchArray($name, $argument, "className")) {
					throw (new Exception("Service 'className' is required in parameter on position ".$position));
				}
				if (gettype($container) != "object") {
					throw (new Exception("The dependency injector container is not valid"));
				}
				if (\Amon\Helper\Other::fetchArray($instanceArguments, $argument, "arguments")) {
					return $container->get($name, $instanceArguments);
				}
				return $container->get($name);
			default:
				throw (new Exception("Unknown service type in parameter on position ".$position));
		}
	}
	/*
	**
		 * Resolves an array of parameters
		 *
	*/
	private function buildParameters($container, $arguments)
	{
		$buildArguments = [];
		foreach ($arguments as $position => $argument) {
			$buildArguments[] = $this->buildParameter($container, $position, $argument);
		}
		return $buildArguments;
	}
}
