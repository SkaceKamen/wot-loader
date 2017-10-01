<?php
namespace Loader\Storage;

class Item
{
	/** @var string $type type id */
	protected $type = '';

	/** @var array $keys list of keys making this item unique */
	protected $keys = array();

	/** @var array $values item values */
	protected $values = array();

	/** @var \SimpleXMLElement $raw raw item data */
	protected $raw = null;

	/** @var boolean $saved saved in database */
	protected $saved = false;

	public function __construct($type, $keys, $values, $saved = false) {
		$this->type = $type;
		$this->keys = $keys;
		$this->saved = $saved;
		$this->update($values);
	}

	public function getType() {
		return $this->type;
	}

	public function getKeys() {
		return $this->keys;
	}

	public function getValues() {
		return $this->values;
	}

	public function get($key) {
		return isset($this->values[$key]) ? $this->values[$key] : null;
	}

	/**
	 * @param Item|array $item
	 */
	public function update($item) {
		if (is_object($item) && $item->isSaved()) {
			$this->saved = true;
		}

		if (is_object($item)) {
			$item = $item->getValues();
		}

		foreach ($item as $key => $value) {
			if (!is_numeric($key))
				$this->values[strtolower($key)] = $value;
		}
	}

	/**
	 * @param Item $item
	 * @return bool
	 */
	public function equals(Item $item) {
		if ($item->getType() != $this->getType())
			return false;

		foreach ($this->getKeys() as $key) {
			if (!$this->compare($this->get($key), $item->get($key)))
				return false;
		}

		return true;
	}

	public function match($array) {
		if (!is_array($array))
			return false;

		foreach ($array as $key => $value) {
			if (!$this->compare($this->get($key), $value))
				return false;
		}

		return true;
	}

	private function compare($myself, $other) {
		if (is_string($myself)) {
			if (strcasecmp($myself, $other) != 0) {
				return false;
			}
		} else if ($myself instanceof Relator) {
			if (!$myself->equals($other))
				return false;
		} else {
			if ($myself != $other) {
				return false;
			}
		}
		return true;
	}

	public function setRaw($raw) {
		$this->raw = $raw;
	}

	public function getRaw() {
		return $this->raw;
	}

	public function isSaved() {
		return $this->saved;
	}

	public function getRelator() {
		$values = array();
		foreach ($this->keys as $key) {
			$values[$key] = $this->get($key);
		}
		return new Relator($this->getType(), $values);
	}

	public function getHash() {
		$hash = array();
		foreach ($this->keys as $key) {
			if ($key != 'wot_version_id') {
				$value = $this->get($key);
				if ($value instanceof Relator) {
					$hash[] = $value->getHash();
				} else {
					$hash[] = $value;
				}
			}
		}
		return strtolower(implode('-', $hash));
	}
}