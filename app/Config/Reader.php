<?php
namespace Loader\Config;


/**
 * @property \Loader\Config\Category mysql
 * @property \Loader\Config\Category paths
 */
class Reader
{
	/** @var \Loader\Config\Category $contents */
    private $contents;

	public function __construct($path = 'config.php') {
		$this->load($path);
	}
	
	public function load($path = 'config.php') {
		if (!file_exists($path)) {
			throw new \Exception("Config path '$path' doesn't exist.");
		}
		
		$this->contents = new Category(require_once($path));
	}
	
	public function __get($name) {
		return $this->contents->$name;
	}
	
	public function __isset($name) {
		return isset($this->contents->$name);
	}
}