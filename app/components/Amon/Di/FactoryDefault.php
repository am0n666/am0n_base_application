<?php

namespace Amon\Di;

class FactoryDefault extends \Amon\Di\Di
{
    public function __construct()
    {
        parent::__construct();
		$this->services = [
			"eventsManager"			=>		(new Service("Amon\\Events\\Manager", TRUE )),
			"errorhandler"			=>		(new \Amon\Error\Handler())->register()
		];
    }
};
