<?php

namespace Amon;

class Exception extends \Exception implements \Throwable
{
    public static function containerServiceNotFound($service)
    {
        return "A dependency injection container is required to access " . $service;
    }

}
