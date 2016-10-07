<?php
namespace Loader\Config;

class ReaderClass
{
	public function __construct($contents) {
		foreach($contents as $key => $value) {
			if (is_array($value)) {
				$contents[$key] = new ReaderClass($value);
			}
		}
		
		$this->contents = $contents;
	}
	
	public function __get($name) {
		if (isset($this->contents[$name]))
			return $this->contents[$name];
		return null;
	}
	
	public function __isset($name) {
		return isset($this->contents[$name]);
	}
}