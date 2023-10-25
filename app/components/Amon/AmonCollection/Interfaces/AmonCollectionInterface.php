<?php

namespace Amon\AmonCollection\Interfaces;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use Amon\Collection\CollectionInterface;

use Amon\Helper\Other;
use Amon\Helper\Json;
use Amon\Helper\Str;

interface AmonCollectionInterface extends Countable, IteratorAggregate
{
	public function init(array $items);
	public function get($key, $default = null);
	public function set($key, $value);
    public function all();
	public function has($key) : bool;
	public function remove($key);
    public function clear();
    public function loadFromJson(string $json_file_path);
    public function saveToJson(string $json_file_path);
	public function count();
	public function getIterator();
	public function getKeys() : array;
	public function getValues() : array;
	public function toArray() : array;
	public function toObject() : object;
	public function toJson($options = 79) : string;
	public function getValuesFromKeyName(string $key_name);
}

