<?php
namespace Loader\Extracted\Reader;

class Chassis extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/chassis.xml',
			'\Loader\Storage\Chassis',
			array()
		);
	}

	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}