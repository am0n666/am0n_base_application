<?php

namespace Amon\AmonCollection;

use Amon\AmonCollection\Interfaces\AmonCollectionInterface;

use Amon\Helper\Other;
use Amon\Helper\Json;
use Amon\Helper\Str;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class AmonCollection implements AmonCollectionInterface, Countable, IteratorAggregate
{
	protected $data = array();
	protected $json_file_path;

    public function __construct(array $items = array(), string $json_file_path = '')
    {
		if (is_file(Str::pathFixSlashes($json_file_path))) {
			$this->json_file_path = Str::pathFixSlashes($json_file_path);
			$this->loadFromJson($this->json_file_path);
		}else{
			$this->init($items);
		}
    }

    public function init(array $items)
    {
		$this->data = array();
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
		return $this;
    }

	public function get($key, $default = null)
	{
		if (is_int($key)) {
			if (isset($this->data[$key])) {
				return $this->data[$key];
			}
		}

		if (is_string($key)) {
			if (isset($this->data[$key]))
				return $this->data[$key];
			return $default;
		}

		return $default;
	}

	public function set($key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}

    public function all()
    {
        return $this->data;
    }

	public function has($key) : bool
	{
		if (is_int($key)) {
			if (isset($this->data[$key])) {
				return(true);
			}
		}

		if (is_string($key)) {
			if (isset($this->data[$key]))
				return(true);
			return(false);
		}
		return(false);
	}

	public function create($key)
	{
		$this->data[] = $key;
		$this->saveToJson($this->json_file_path);
		return $this;
	}

	protected function setData($element, $value)
	{
		$this->data[$element] = $value;
	}

	public function remove($key)
	{
		if ($this->has($key)) {
			$data = $this->data;
			unset($data[$key]);
			$data = array_values($data);
			$this->clear();
			if (is_file($this->json_file_path)) {
				$this->data = $data;
				return $this->saveToJson($this->json_file_path);
			}else{
				return $this->init($data);
			}
		}
	}

    public function clear()
    {
        $this->data = array();
    }

    public function loadFromJson(string $json_file_path)
    {
       $result = null;
        if (is_file(Str::pathFixSlashes($json_file_path))) {
			$this->json_file_path = Str::pathFixSlashes($json_file_path);
            $result = Json::decode(file_get_contents(Str::pathFixSlashes($json_file_path)), true);
            if (count($result) === 0) {
                if (unlink(Str::pathFixSlashes($json_file_path))) {
                    return(false);
                }
            }
			$this->json_file_path = Str::pathFixSlashes($json_file_path);
            $this->init($result);
        }
        return(false);
    }

    public function saveToJson(string $json_file_path)
    {
        if (is_array($this->data)) {
            if (count($this->data) === 0) {
                if (unlink(Str::pathFixSlashes($json_file_path))) {
                    return(false);
                }
            }elseif ((count($this->data) > 0)) {
				if (file_put_contents(Str::pathFixSlashes($json_file_path), Json::encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) {
					$this->init($this->data);
				}
			}
        }
        return(false);
    }

	public function count()
	{
		return count($this->data);
	}

	public function getIterator()
	{
		return (new ArrayIterator($this->data));
	}

	public function getKeys() : array
	{
			return array_keys($this->data);
	}

	public function getValues() : array
	{
		return array_values($this->data);
	}

	public function toArray() : array
	{
		return Other::toArray($this);
	}

	public function toObject() : object
	{
		return Other::toObject($this);
	}

	public function toJson($options = 79) : string
	{
		return Json::encode($this->toArray(), $options);
	}

	public function getValuesFromKeyName(string $key_name)
	{
		$result = false;
		$i = 0;
		foreach ($this->data as $key => $val) {
			if (is_array($key)) {
				$result[$i] = $this->getValuesFromKeyName($key_name);
			}
			if (isset($this->data[$i][$key_name])) {
				$result[$i] = $this->data[$i][$key_name];
			}
			$i++;
		}
		return $result;
	}
}
