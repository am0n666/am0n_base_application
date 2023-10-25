<?php

namespace Amon\Error;

class Error
{
    public $attributes;

    public function __construct(array $options = [])
    {
        $defaults = [
            'type'        => -1,
            'message'     => 'No error message',
            'file'        => '',
            'line'        => '',
            'exception'   => null,
            'isException' => false,
            'isError'     => false,
        ];

        $options = array_merge($defaults, $options);

        foreach ($options as $option => $value) {
            $this->attributes[$option] = $value;
        }
    }

    public function __call($method, $args)
    {
        return isset($this->attributes[$method]) ? $this->attributes[$method] : null;
    }
}
