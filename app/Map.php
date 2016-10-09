<?php
namespace Loader;

class Map
{
	private $values;
	private $keys;
	
	public function __construct() {
		$this->values = array();
		$this->keys = array();
	}
	
	public function set($key, $value) {
		$this->values[$key] = $value;
		$this->keys[$key] = true;
		return $value;
	}
	
	public function get($key, $default = null) {
		if ($this->has($key))
			return $this->values[$key];
		
		trigger_error("Undefined key '$key'", E_USER_NOTICE);
		
		return $default;
	}
	
	public function has($key) {
		return isset($this->keys[$key]);
	}
}