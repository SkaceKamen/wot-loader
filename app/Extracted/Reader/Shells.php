<?php
namespace Loader\Extracted\Reader;

class Shells extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/shells.xml',
			'Storage\Shell',
			array(
				'name' => 'userString',
				'price' => function($element, $key) {
					if (!isset($element->price->gold))
						return (int)$element->price;
					return (int)$element->price * 400;
				},
				'price_gold' => function($element, $key) {
					if (isset($element->price->gold))
						return (int)$element->price;
					return 0;
				},
				'shell_type' => function($element, $key) {
					switch((string)$element->kind) {
						case 'ARMOR_PIERCING': return 'ap'; break;
						case 'ARMOR_PIERCING_CR': return 'apcr'; break;
						case 'HIGH_EXPLOSIVE': return 'he'; break;
						case 'HOLLOW_CHARGE': return 'heat'; break;
					}
					throw new \Exception('Uknown shell type ' . (string)$element->kind);
				},
				'shell_caliber' => 'caliber',
				'shell_damage_armor' => function($element, $key) { return (int)$element->damage->armor; },
				'shell_damage_device' => function($element, $key) { return (int)$element->damage->devices; },
				'shell_explosion_radius' => 'explosionRadius',
				'shell_tracer' => function($element, $key) { return (string)$element->isTracer == 'true' ? 1 : 0; }
			),
			false
		);
	}
	
	public function read($path, $nation, $version) {
		return $this->itemsReader->read($path, $nation, $version);
	}
}