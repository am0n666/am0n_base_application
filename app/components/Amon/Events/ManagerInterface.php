<?php

namespace Amon\Events;

interface ManagerInterface {
	public function attach($eventType, $handler);

	public function detach($eventType, $handler);

	public function detachAll($type = null);

	public function fire($eventType, $source, $data = null , $cancelable = true);

	public function getListeners($type);

	public function hasListeners($type);
}
?>
