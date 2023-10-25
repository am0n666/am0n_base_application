<?php

namespace Amon\Di;

interface InjectionAwareInterface
{
	public function getDI() ;

	public function setDI($container) ;
}
