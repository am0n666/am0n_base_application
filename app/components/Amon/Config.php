<?php

namespace Amon;

use Amon\Helper\Other;

use Amon\AmonCollection\AmonCollection;

class Config extends AmonCollection
{
	const DEFAULT_PATH_DELIMITER = ".";

	protected $pathDelimiter = NULL;

	public function getPathDelimiter()
	{
		if (!$this->pathDelimiter) {
			$this->pathDelimiter = self::DEFAULT_PATH_DELIMITER;
		}
		return $this->pathDelimiter;
	}

	final protected function internalMerge($source, $target)
	{
		foreach ($target as $key => $value) {
			if (gettype($value) === "array" && array_key_exists($key, $source) && gettype($source[$key]) === "array") {
				$source[$key] = $this->internalMerge($source[$key], $value);
			} else if (gettype($key) === "int") {
				$source[] = $value;
			} else {
				$source[$key] = $value;
			}
		}
		return $source;
	}

	public function merge($toMerge)
	{
		if (gettype($toMerge) === "array") {
			$config = (new Config($toMerge));
		} else if (gettype($toMerge) === "object" && $toMerge instanceof ConfigInterface) {
			$config = $toMerge;
		} else  {
			throw (new \Exception("Invalid data type for merge."));
		}
		$source = Other::toArray();
		$target = Other::toArray();
		$result = $this->internalMerge($source, $target);
		$this->clear();
		$this->init($result);
		return $this;
	}

	public function path($path, $defaultValue = NULL, $delimiter = NULL)
	{
		if ($this->has($path)) {
			return $this->get($path);
		}
		if (Other::isEmpty(($delimiter))) {
			$delimiter = $this->getPathDelimiter();
		}
		$config = clone $this;
		$keys = explode($delimiter, $path);
		while ((!Other::isEmpty(($keys)))) {
			$key = array_shift($keys);
			if (!$config->has($key)) {
				break;
			}
			if (Other::isEmpty(($keys))) {
				return $config->get($key);
			}
			$config = $config->get($key);
			if (Other::isEmpty(($config))) {
				break;
			}
		}
		return $defaultValue;
	}

	protected function setData($element, $value)
	{
		$element = (string)$element;
		$key = $this->insensitive ? mb_strtolower($element) : $element;
		// $this->lowerKeys[$key] = $element;
		if (gettype($value) === "array") {
			$data = (new Config($value));
		} else {
			$data = $value;
		}
		$this->data[$element] = $data;
	}

	public function setPathDelimiter($delimiter = NULL)
	{
		$this->pathDelimiter = $delimiter;
		return $this;
	}

	// public function toArray()
	// {
		// $results = [];
		// $data = parent::toArray();
		// foreach ($data as $key => $value) {
			// if (gettype($value) === "object" && method_exists($value, "toArray")) {
				// $value = $value->toArray();
			// }
			// $results[$key] = $value;
		// }
		// return $results;
	// }
}
