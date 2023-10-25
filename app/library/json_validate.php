<?php

function json_validate(string $json, int $depth = 512, int $flags = 0): bool
{
	if (is_string($json) && $json !== '') 
	{
		@json_decode($json, null, $depth, $flags);
		if (json_last_error() === JSON_ERROR_NONE) {
			return true;
		}
	}
	return false;
}