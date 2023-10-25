<?php

if(!function_exists('v'))   { function v() { foreach(func_get_args() as $each){ var_dump($each); }    } }
if(!function_exists('vx'))  { function vx(){ foreach(func_get_args() as $each){ var_dump($each); } exit;} }

function eecho($value) {
	echo $value . "<br>";
}