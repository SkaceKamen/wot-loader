<?php
namespace Loader;

class Storage
{
    /** @var Storage\Item[][] */
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
		
		if (!isset($this->types[$type]))
			return null;
		
		foreach($this->types[$type] as $existing) {
			if ($existing->equals($item))
				return $existing;
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
		
		$this->types[$type][] = $item;
	}
	
	public function save() {
		throw new \Exception("This is supposed to be implemented.");
	}
}