<?php

namespace Amon\Helper;

use InvalidArgumentException;

class Json
{

	final public static function decode($data, $associative = FALSE, $depth = 512, $options = 0)
	{
		$decoded = json_decode($data, $associative, $depth, $options);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw (new InvalidArgumentException("json_decode error: " . json_last_error_msg()));
		}
		return $decoded;
	}

	final public static function encode($data, $options = 0, $depth = 512)
	{
		$encoded = json_encode($data, $options, $depth);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw (new InvalidArgumentException("json_encode error: " . json_last_error_msg()));
		}
		return $encoded;
	}
}
