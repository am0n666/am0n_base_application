<?php

namespace Amon\Di;

abstract class AbstractInjectionAware implements InjectionAwareInterface
{
	protected $container;

	public function getDI() {
		return $this->container;
	}

	public function setDI($container) {
		$this->container = $container;
	}
}
?>
