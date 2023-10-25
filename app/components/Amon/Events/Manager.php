<?php

namespace Amon\Events;

use Closure;
use SplPriorityQueue;
use Amon\Helper\Other;

class Manager
	implements ManagerInterface {

	const DEFAULT_PRIORITY=100;

	protected $collect= FALSE ;
	protected $enablePriorities= FALSE ;
	protected $events= NULL ;
	protected $responses;

	public	function arePrioritiesEnabled (	)	{
		return $this->enablePriorities;
	}

	public function attach ( $eventType, $handler, $priority=self::DEFAULT_PRIORITY ) {
		if ( FALSE ===$this->isValidHandler($handler)) {
			throw (new Exception("Event handler must be an Object or Callable"));
		}
		if (!\Amon\Helper\Other\fetchArray($priorityQueue,$this->events,$eventType)) {
			$priorityQueue=(new SplPriorityQueue());
			$priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
			$this->events[$eventType]=$priorityQueue;
		}
		if (!$this->enablePriorities) {
			$priority=self::DEFAULT_PRIORITY;
		}
		$priorityQueue->insert($handler,$priority);
	}

	public function collectResponses ( $collect ) {
		$this->collect=$collect;
	}

	public function detach ( $eventType, $handler ) {
		if ( FALSE ===$this->isValidHandler($handler)) {
			throw (new Exception("Event handler must be an Object or Callable"));
		}
		if (\Amon\Helper\Other\fetchArray($priorityQueue,$this->events,$eventType)) {
			$newPriorityQueue=(new SplPriorityQueue());
			$newPriorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
			$priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
			$priorityQueue->top();
			while ($priorityQueue->valid()) {
				$data=$priorityQueue->current();
				$priorityQueue->next();
				if ($data["data"]!==$handler) {
					$newPriorityQueue->insert($data["data"],$data["priority"]);
				}
			}$this->events[$eventType]=$newPriorityQueue;
		}
	}

	public function detachAll ( $type= NULL ) {
		if ($type=== NULL ) {
			$this->events= NULL ;
		} else	{
			if (\Amon\Helper\Other\issetArray($this->events,$type)) {
				unset ($this->events[$type]);
			}
		}
	}

	public function enablePriorities ( $enablePriorities ) {
		$this->enablePriorities=$enablePriorities;
	}

	public function fire ( $eventType, $source, $data= NULL , $cancelable= TRUE ) {
		$events=$this->events;
		if ( gettype ($events)!="array") {
			return	NULL ;
		}
		if (!memstr($eventType,":")) {
			throw (new Exception("Invalid event type ".$eventType));
		}
		$eventParts=explode(":",$eventType);
		$type=$eventParts[0];
		$eventName=$eventParts[1];
		$status= NULL ;
		if ($this->collect) {
			$this->responses= NULL ;
		}
		$event=(new Event($eventName,$source,$data,$cancelable));
		if (\Amon\Helper\Other\fetchArray($fireEvents,$events,$type)) {
			if ( gettype ($fireEvents)=="object") {
				$status=$this->fireQueue($fireEvents,$event);
			}
		}
		if (\Amon\Helper\Other\fetchArray($fireEvents,$events,$eventType)) {
			if ( gettype ($fireEvents)=="object") {
				$status=$this->fireQueue($fireEvents,$event);
			}
		}
		return $status;
	}

	final public function fireQueue ( $queue, $event ) {
		$status= NULL ;
		$eventName=$event->getType();
		if ( gettype ($eventName)!="string") {
			throw (new Exception("The event type not valid"));
		}
		$source=$event->getSource();
		$data=$event->getData();
		$cancelable=(bool)$event->isCancelable();
		$collect=(bool)$this->collect;
		$iterator= clone $queue;
		$iterator->top();
		while ($iterator->valid()) {
			$handler=$iterator->current();
			$iterator->next();
			if ( FALSE ===$this->isValidHandler($handler)) {
				continue ;
			}
			if ($handler instanceof Closure||is_callable($handler)) {
				$status=call_user_func_array($handler,[
				$event,$source,$data]);
			} else	{
				if (!method_exists($handler,$eventName)) {
					continue ;
				}
				$status=$handler->$eventName($event,$source,$data);
			}
			if ($collect) {
				$this->responses[]=$status;
			}
			if ($cancelable) {
				if ($event->isStopped()) {
					break ;
				}
			}
		} return $status;
	}

	public function getListeners ( $type ) {
		if (!\Amon\Helper\Other\fetchArray($fireEvents,$this->events,$type)) {
			return [];
		}
		$listeners=[];
		$priorityQueue= clone $fireEvents;
		$priorityQueue->top();
		while ($priorityQueue->valid()) {
			$listeners[]=$priorityQueue->current();
			$priorityQueue->next();
		} return $listeners;
	}

	public function getResponses ( ) {
		return $this->responses;
	}

	public function hasListeners ( $type ) {
		return \Amon\Helper\Other\issetArray($this->events,$type);
	}

	public function isCollecting ( ) {
		return $this->collect;
	} public	function isValidHandler ( $handler )	{
		if ( gettype ($handler)!="object"&&!is_callable($handler)) {
			return	FALSE ;
		}
		return	TRUE ;
	}
}
?>
