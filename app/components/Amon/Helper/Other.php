<?php

namespace Amon\Helper;

class Other
{
	final public static function getArrayKey($array, $index)
	{
		if (isset($array) && isset($index)) {
			switch (gettype($index)) {
				case 'integer':
					if (isset($array[$index]))
						return $array[$index];
				case 'NULL':
					return false;
				case 'string':
					if (isset($array[$index]))
						return $array[$index];
				default:
					return false;
			}
		}
		return false;
	}

	final public static function _array_values(array $data, string $key_name)
	{
		$result = false;
		$i = 0;
		foreach ($data as $key => $val) {
			if (is_array($val)) {
				$result[$i] = self::_array_values($val, $key_name);
			}
			if (isset($val[$key_name])) {
				$result[$i] = $val[$key_name];
			}
			$i++;
		}
		return $result;
	}

	final public static function issetArray($array, $index)
	{
		if (isset($array) && isset($index)) {
			switch (gettype($index)) {
				case 'double':
					$index = (int) $index;
					// no break
				case 'boolean':
				case 'integer':
				case 'resource':
					return isset($array[$index]);
				case 'NULL':
					$index = '';
					// no break
				case 'string':
					return array_key_exists($index, $array);
				default:
					throw new \Exception('Illegal offset type');
			}
		}
		return false;
	}

	final public static function fetchArray(&$result, $array, $index)
	{
		if (array_key_exists($index, $array)) {
			$result = $array[$index];
			return true;
		}
		return false;
	}

    final public static function isEmpty($var)
    {
        if ($var ?? false) {
            return false;
        }
        if (isset($var) && ($var !== null)) {
            if (is_bool($var)) {
                return $var === false;
            } elseif (is_string($var)) {
                return strlen($var) === 0;
            }
            return ((bool) $var) === false;
        }
        return true;
    }

    final public static function toArray($collection)
    {
        $result = null;
        if (self::isempty($collection)) {
            return $result;
        }
        if (isset($collection)) {
            $result_obj = [];
            foreach($collection as $key => $val) {
                $type = ucfirst(gettype($val));
                if($type == "Array") {
                    $result_obj[$key] = (array) self::toArray($val);
                } elseif($type == "Object") {
                    $result_obj[$key] = (array) self::toArray($val);
                } else {
                    $result_obj[$key] = $val;
                }
            }
            $result = (array) $result_obj;
        }
        return (array) $result;
    }

    final public static function toObject($collection)
    {
        $result = null;
        if (self::isempty($collection)) {
            return $result;
        }
        if (isset($collection)) {
            $result_obj = [];
            foreach($collection as $key => $val) {
                $type = ucfirst(gettype($val));
                if($type == "Array") {
                    $result_obj[$key] = (object) self::toObject($val);
                } elseif($type == "Object") {
                    $result_obj[$key] = (object) self::toObject($val);
                } else {
                    $result_obj[$key] = $val;
                }
            }
            $result = (array) $result_obj;
        }
        return (object) $result;
    }

    final public static function validateDate($date, $format = 'Y-m-d'){
		$d = \DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}

	// Format: 'Y-m-d'
    final public static function dataPL($date)
    {
		if (self::validateDate($date)) {
			$miesiace_pl = ['stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia'];
			$_y = explode("-", $date)[0];
			$_m = (explode("-", $date)[1] - 1);
			$_d = explode("-", $date)[2];
			return $_d . " " . $miesiace_pl[$_m] . " " . $_y;
		}
		return(false);
    }

	final public static function createInstance($class)
	{
		if (!isset($class) || !is_string($class)) { // YES
			throw new \Exception("Invalid class name");
		}
	
		if (!class_exists($class)) { // YES
			throw new \Exception("Class [{$class}] does not exist");
		}
	
		return new $class();
	}
	
	final public static function createInstanceParams($class, $parameters)
	{
		if (!isset($class) || !is_string($class)) { // YES
			throw new \Exception("Invalid class name");
		}
	
		if (!isset($parameters) || !is_array($parameters)) { // YES
			throw new \Exception("Instantiation parameters must be an array");
		}
	
		if (!class_exists($class)) { // YES
			throw new \Exception("Class [{$class}] does not exist");
		}
	
		$re_args = [];
		$refMethod = new \ReflectionMethod($class, '__construct');
		foreach ($refMethod->getParameters() as $key => $param) {
			if ($param->isPassedByReference()) {
				$re_args[$key] = &$parameters[$key];
			} else {
				$re_args[$key] = &$parameters[$key];
			}
		}
	
		$refClass = new \ReflectionClass($class);
		return $refClass->newInstanceArgs((array) $re_args);
	}

}
