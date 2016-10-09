<?php
namespace Loader\Extracted\Reader;

class Radios extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/radios.xml',
			'\Loader\Storage\Radio',
			array(
				'name' => 'userString',
				'level' => 'level',
				'price' => 'price',
				'weight' => 'weight',
				'health' => 'maxHealth',
				'health_regen' => 'maxRegenHealth',
				'repair' => 'repairCost',
				'radio_distance' => 'distance'
			)
		);
	}

	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}