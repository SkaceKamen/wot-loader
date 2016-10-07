<?php
namespace Loader\Extracted\Reader;

class This
{
	private $type;
	private $version;
	
	public function __construct($type, $version) {
		$this->type = $type;
		$this->version = $version;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getVersion() {
		return $this->version;
	}
}