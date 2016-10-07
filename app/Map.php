<?php
namespace Loader;

class Map
{
	public function __construct() {
		$this->values = array();
	}
	
	public function set($key, $value) {
		$this->values[$key] = $value;
		return $value;
	}
	
	public function get($key, $default = null) {
		if ($this->has($key))
			return $this->values[$key];
		echo "Warning: undefined key '$key'\r\n";
		return $default;
	}
	
	public function has($key) {
		return (isset($this->values[$key]) || array_key_exists($key, $this->values));
	}
}