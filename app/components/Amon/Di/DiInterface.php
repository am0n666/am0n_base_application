<?php

namespace Amon\Di;

use ArrayAccess;

interface DiInterface extends ArrayAccess
{
	public function attempt($name, $definition, $shared = false) ;

	public function get($name, $parameters = null) ;

	public static function getDefault() ;

	public function getRaw($name) ;

	public function getService($name) ;

	public function getServices() ;

	public function getShared($name, $parameters = null) ;

	public function has($name) ;

	public function remove($name) ;

	public static function reset() ;

	public function set($name, $definition, $shared = false) ;

	public static function setDefault($container) ;

	public function setService($name, $rawDefinition) ;

	public function setShared($name, $definition) ;
}
