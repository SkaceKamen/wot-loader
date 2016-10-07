<?php
namespace Loader\Extracted\Reader;

class Fueltanks extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/fueltanks.xml',
			'\Loader\Storage\Fueltank',
			array(
				'name' => 'userString',
				'nation' => 'nation',
				'price' => 'price',
				'weight' => 'weight',
				'health' => 'maxHealth',
				'health_regen' => 'maxRegenHealth',
				'repair' => 'repairCost'
			)
		);
	}
	
	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}