<?php
namespace Loader\Extracted\Reader;

class Guns extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/components/guns.xml',
			'\Loader\Storage\Gun',
			array(
				'name' => 'userString',
				'level' => 'level',
				'price' => 'price',
				'weight' => 'weight',
				'health' => 'maxHealth',
				'health_regen' => 'maxRegenHealth',
				'repair' => 'repairCost',
				'gun_max_ammo' => 'maxAmmo',
				'gun_impulse' => 'impulse',
				'gun_pitch_limits' => array($this, 'getPitchLimits'),
				'gun_rotation_speed' => array('rotationSpeed'),
				'gun_reload_time' => array('reloadTime'),
				'gun_aiming_time' => array('aimingTime'),
				'gun_clip_count' => array('clip', 'count'),
				'gun_clip_rate' => array('clip', 'rate'),
				'gun_burst_count' => array('burst', 'count'),
				'gun_burst_rate' => array('burst', 'rate'),
				'gun_dispersion_radius' => 'shotDispersionRadius',
				'gun_dispersion_turret_rotation' => array('shotDispersionFactors', 'turretRotation'),
				'gun_dispersion_after_shot' => array('shotDispersionFactors', 'afterShot'),
				'gun_dispersion_damaged' => array('shotDispersionFactors', 'whileGunDamaged'),
				'turret_yaw_limits' => array('turretYawLimits')
			)
		);
	}
	
	public function read($path, $nation, $version) {
		$guns = $this->itemsReader->read($path, $nation, $version);
		$items = $guns;
		
		foreach($guns as $gun) {
			$element = $gun->getRaw();
			
			$shells = new Subitems(
				$this->reader, 'StorageGunShell',
				array(
					'wot_items_guns_id' => $gun->getRelator()
				), array(
					'wot_items_shells_id' => new This('shell', $version),
					'shell_default_portion' => 'defaultPortion',
					'shell_speed' => 'speed',
					'shell_max_distance' => 'maxDistance',
					'shell_piercing_power' => 'piercingPower'
				),
				false
			);
			
			$items = array_merge($items, $shells->read($element->shots, $nation));
		}
		
		return $items;
	}
}