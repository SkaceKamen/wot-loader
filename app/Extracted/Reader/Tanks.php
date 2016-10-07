<?php
namespace Loader\Extracted\Reader;

class Tanks extends Item
{
	public function __construct() {
		$this->itemsReader = new Items(
			'/list.xml',
			'Storage\Tank',
			array(
				'name' => 'userString',
				'level' => 'level',
				'nation' => 'nation',
				'class' => function($element, $key) {
					$tags = preg_split('/\s+/', (string)$element->tags);

					switch($tags[0]) {
						case 'lightTank': return 'light';
						case 'mediumTank': return 'medium';
						case 'heavyTank': return 'heavy';
						case 'AT-SPG': return 'td';
						case 'SPG': return 'spg';
					}
				},
				'secret' => function($element, $key) {
					return in_array('secret', preg_split('/\s+/', (string)$element->tags)) ? 1 : 0;
				},
				'igr' => function($element, $key) {
					return in_array('premiumIGR', preg_split('/\s+/', (string)$element->tags)) ? 1 : 0;
				},
				'tags' => function($element, $key) {
					return implode(' ', preg_split('/\s+/', (string)$element->tags));
				},
				'price' => function($element, $key) {
					if (!isset($element->price->gold))
						return (int)$element->price;
					return 0;
				},
				'price_gold' => function($element, $key) {
					if (isset($element->price->gold))
						return (int)$element->price;
					return 0;
				}
			),
			false
		);
	}
	
	public function read($path, $nation, $version) {
		$tanks = $this->itemsReader->read($path, $nation, $version);
		
		$items = $tanks;
		
		//@TODO: This section could be nicer
		
		$no_tanks = array('list.xml','components','customization.xml');
		$path = $path . '/' . $nation . '/';
		$dir = opendir($path);

		while($file = readdir($dir)) {
			if (!is_dir($path . $file) &&
				$file!='.' && $file!='..' && !in_array($file,$no_tanks)) {

				$name = $file;
				
				$node = substr($name,0,strpos($name,'.'));
				$node = $nation . '-' . $node;
				
				$file = $path . $file;
				
				$str = file_get_contents($file);
				$str = str_replace("shared", "", $str);

				$f = fopen($file, 'w');
				fwrite($f,$str);
				fclose($f);

				$list = simplexml_load_file($file);
				
				$item = null;
				foreach($tanks as $tank) {
					if ($tank->get('name_node') == $node) {
						$item = $tank;
						break;
					}
				}
				
				if ($item === null) {
					echo "<b>Tank $node is not listed in list.xml!</b>\r\n";
					
					$item = new Storage\Tank(array());
					$items[] = $item;
				}
				
				$item->update(array(
					'nation' => $nation,
					'wot_version_id' => $version,
					'name_node' => $node,
					'health' => (int)$list->hull->maxHealth,
					'speed_forward' => (int)$list->speedLimits->forward,
					'speed_backward' => (int)$list->speedLimits->backward,
					'repair' => (int)$list->repairCost,
					'weight' => (float)$list->hull->weight,
					'armor' => $this->getArmorString($list->hull),
					'armor_primary' => $this->getPrimaryArmorString($list->hull),
					'ammo_health' => (int)$list->hull->ammoBayHealth->maxHealth,
					'ammo_health_regen' => (int)$list->hull->ammoBayHealth->maxRegenHealth,
					'ammo_repair' => (float)$list->hull->ammoBayHealth->repairCost,
					'crew' => $this->getCrew($list)
				));
				
				$turrets = new Subitems(
					$this->reader, 'Storage\Turret',
					array(
						'wot_version_id' => $version,
						'wot_tanks_id' => $item->getRelator()
					), array(
						'name' => 'userString',
						'level' => 'level',
						'price' => 'price',
						'weight' => 'weight',
						'health' => 'maxHealth',
						'turret_yaw_limits' => 'yawLimits',
						'turret_armor' => array($this, 'getArmorString'),
						'turret_armor_primary' => array($this, 'getPrimaryArmorString'),
						'turret_rotation_speed' => 'rotationSpeed',
						'turret_rotator_health' => array('turretRotatorHealth', 'maxHealth'),
						'turret_rotator_health_regen' => array('turretRotatorHealth', 'maxRegenHealth'),
						'turret_rotator_repair' => array('turretRotatorHealth', 'repairCost'),
						'turret_vision_radius' => 'circularVisionRadius',
						'turret_scope_health' => array('surveyingDeviceHealth', 'maxHealth'),
						'turret_scope_health_regen' => array('surveyingDeviceHealth', 'maxRegenHealth'),
						'turret_scope_repair' => array('surveyingDeviceHealth', 'repairCost')
					),
					true,
					$item
				);
				
				$turrets = $turrets->read($list->turrets0, $nation);
				$items = array_merge($items, $turrets);
				
				foreach($turrets as $turret) {
					
					// Unlocks can get into array
					if ((!$turret instanceof Storage\Turret))
						continue;
						
					$raw = $turret->getRaw();

					$turret_guns = new Subitems(
						$this->reader, 'Storage\Turret\Gun',
						array(
							'wot_items_turrets_id' => $turret->getRelator()
						), array(
							'wot_items_guns_id' => new SelfRelator('gun', $version),
							'gun_armor' => array($this, 'getArmorString'),
							'gun_armor_gun' => function($element, $key) {
								foreach($element->armor->children() as $n => $c) {
									if ($n == 'gun') {
										return (int)$c;
									}
								}
								return null;
							},
							'gun_max_ammo' => 'maxAmmo',
							'gun_aiming_time' => 'aimingTime',
							'gun_reload_time' => 'reloadTime',
							'gun_dispersion_radius' => 'shotDispersionRadius',
							'gun_dispersion_turret_rotation' => array('shotDispersionFactors', 'turretRotation'),
							'gun_dispersion_after_shot' => array('shotDispersionFactors', 'afterShot'),
							'gun_dispersion_damaged' => array('shotDispersionFactors', 'whileGunDamaged'),
							'turret_yaw_limits' => function($element) {
								if (isset($element->turretYawLimits))
									return (string)$element->turretYawLimits;
								return null;
							},
							'gun_pitch_limits' => array($this, 'getPitchLimits'),
							'gun_clip_count' => function($element) {
								if (isset($element->clip))
									return (int)$element->clip->count;
								return null;
							},
							'gun_clip_rate' => function($element) {
								if (isset($element->clip))
									return (int)$element->clip->rate;
								return null;
							},
						),
						false,
						$item
					);
					
					$items = array_merge($items, $turret_guns->read($raw->guns, $nation));
				}
					
				$chassis = new SubitemsReader(
					$this->reader, 'Storage\Chassis',
					array(
						'wot_version_id' => $version,
						'wot_tanks_id' => $item->getRelator()
					), array(
						'name' => 'userString',
						'level' => 'level',
						'price' => 'price',
						'weight' => 'weight',
						'health' => 'maxHealth',
						'health_regen' => 'maxRegenHealth',
						'repair' => 'repairCost',
						'chassis_armor_left' => array('armor', 'leftTrack'),
						'chassis_armor_right' => array('armor', 'rightTrack'),
						'chassis_climb_edge' => 'maxClimbAngle',
						'chassis_load' => 'maxLoad',
						'chassis_brake' => 'brakeForce',
						'chassis_rotation_speed' => 'rotationSpeed',
						'chassis_bulk_health' => 'bulkHealthFactor',
						'chassis_terrain_resistance' => 'terrainResistance',
						'chassis_gun_dispersion_movement' => array('shotDispersionFactors', 'vehicleMovement'),
						'chassis_gun_dispersion_rotation' => array('shotDispersionFactors', 'vehicleRotation')
					),
					true,
					$item
				);
					
				$engines = new SubitemsReader(
					$this->reader, 'Storage\Tank\Engine',
					array(
						'wot_tanks_id' => $item->getRelator()
					), array(
						'wot_items_engines_id' => new SelfRelator('engine', $version)
					),
					false,
					$item
				);
				
				$radios = new SubitemsReader(
					$this->reader, 'Storage\Tank\Radio',
					array(
						'wot_tanks_id' => $item->getRelator()
					), array(
						'wot_items_radios_id' => new SelfRelator('radio', $version)
					),
					false,
					$item
				);
				
				$items = array_merge($items, $chassis->read($list->chassis, $nation));
				$items = array_merge($items, $engines->read($list->engines, $nation));
				$items = array_merge($items, $radios->read($list->radios, $nation));
				
				$subitems = $list->fuelTanks->children();
				foreach($subitems as $node => $content) {
					$node = $nation . '-' . $node;
					
					$item->update(array('default_tank' => new Storage\Relator('fuel_tank', array(
						'name_node' => $node,
						'nation' => $nation,
						'wot_version_id' => $version
					))));
				}
				
				//@TODO: Add unlocks
				
			}
		}
		
		return $items;
	}
}
