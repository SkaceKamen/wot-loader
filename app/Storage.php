<?php
namespace Loader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Storage implements LoggerAwareInterface
{
	use LoggerAwareTrait;

    /** @var Storage\Item[][] $types */
	protected $types = array();

	public function setItem($item) {
		$existing = $this->findItem($item);
		if (!$existing) {
			$this->saveItem($item);
		} else {
			$existing->update($item);
		}
	}

    /**
     * @param Storage\Item $item
     * @return Storage\Item
     */
	public function findItem($item) {
		$type = $item->getType();
		$hash = $item->getHash();
		
		if (!isset($this->types[$type])) {
			return null;
		}
		
		if (isset($this->types[$type][$hash])) {
			return $this->types[$type][$hash];
		}
		
		return null;
	}

    /**
     * @param Storage\Item $item
     */
	private function saveItem(Storage\Item $item) {
		$type = $item->getType();
		
		if (!isset($this->types[$type]))
			$this->types[$type] = array();
		
		$this->types[$type][$item->getHash()] = $item;
	}
	
	public function save() {
		throw new \Exception("This is supposed to be implemented.");
	}
}