<?php
namespace Loader\Extracted\Reader;

class Turrets extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/turrets.xml',
			'Storage\Turret',
			array()
		);
	}
	
	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}