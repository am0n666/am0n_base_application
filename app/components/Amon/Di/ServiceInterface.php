<?php

namespace Amon\Di;

interface ServiceInterface
{
	public function getDefinition() ;

	public function getParameter($position) ;

	public function isResolved() ;

	public function isShared() ;

	public function resolve($parameters = null, $container = null) ;

	public function setDefinition($definition) ;

	public function setParameter($position, $parameter) ;

	public function setShared($shared) ;
}
