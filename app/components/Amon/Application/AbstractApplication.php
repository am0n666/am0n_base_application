<?php

namespace Amon\Application;

use Amon\Di\DiInterface;
use Amon\Di\Injectable;

abstract class AbstractApplication extends Injectable
{

  protected $container;

  public  function __construct ( $container = null  )  {
	  if (is_object($container)) {
		  $this->container = $container;
	  }
  }

}
?>
