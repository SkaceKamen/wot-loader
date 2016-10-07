<?php
namespace Loader\Extracted\Reader;

class Engines extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/engines.xml',
			'\Loader\Storage\Engine',
			array(
				'name' => 'userString',
				'level' => 'level',
				'price' => 'price',
				'weight' => 'weight',
				'health' => 'maxHealth',
				'health_regen' => 'maxRegenHealth',
				'repair' => 'repairCost',
				'engine_power' => 'power',
				'engine_fire_chance' => 'fireStartingChance'
			)
		);
	}
	
	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}