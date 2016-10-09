<?php
namespace Loader\Storage;

class Relator
{
	private $type;
	private $values;

	public function __construct($type, $values) {
		$this->type = $type;
		$this->values = $values;

		if (!$this->values) {
			throw new \Exception("Creating empty relator.");
		}
	}

	public function getType() {
		return $this->type;
	}

	public function getValues() {
		return $this->values;
	}

	public function get($key) {
		return isset($this->values[$key]) ? $this->values[$key] : null;
	}

	/**
	 * @param Relator $relator
	 * @return bool
	 */
	public function equals($relator) {
		if ($relator === null)
			return false;

		if ($relator->getType() != $this->getType() ||
			count($relator->getValues()) != count($this->getValues())
		)
			return false;

		foreach ($relator->getValues() as $key => $value) {
			if (is_string($value)) {
				if (strcasecmp($value, $this->get($key)) != 0)
					return false;
			} else {
				if ($value != $this->get($key))
					return false;
			}
		}

		return true;
	}

	public function getHash() {
		$hash = array();
		foreach ($this->getValues() as $key => $value) {
			$hash[] = $value;
		}
		return implode('-', $hash);
	}
}