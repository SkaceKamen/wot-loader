<?php
namespace Loader\Storage;

use Loader\Map;
use Loader\Storage;
use Loader\Database;
use Psr\Log\LogLevel;

class Mysql extends Storage
{
    /** @var Database $db */
	protected $db;

	public function __construct(Database $db) {
		$this->db = $db;
		$this->db->autocommit(false);
	}

	public function preload($version_id) {
		$chassis = new Map();
		$engines = new Map();
		$turrets = new Map();
		$guns = new Map();
		$shells = new Map();
		$radios = new Map();
		$fuelTanks = new Map();
		$tanks = new Map();
		$equipment = new Map();

		$query = $this->db->query("SELECT * FROM wot_items_tanks WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $fuelTanks->set($row['wot_items_tanks_id'], new Fueltank($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_tanks WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $tanks->set($row['wot_tanks_id'], new Tank($row, true));

			$item->update(array(
				'default_tank' => $fuelTanks->get($row['default_tank'], new None())->getRelator()
			));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_chassis WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $chassis->set($row['wot_items_chassis_id'], new Chassis($row, true));

			if ($row['wot_tanks_id']) {
				$item->update(array(
					'wot_tanks_id' => $tanks->get($row['wot_tanks_id'], new None())->getRelator()
				));
			}

			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_turrets WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $turrets->set($row['wot_items_turrets_id'], new Turret($row, true));

			if ($row['wot_tanks_id']) {
				$item->update(array(
					'wot_tanks_id' => $tanks->get($row['wot_tanks_id'], new None())->getRelator()
				));
			}

			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_guns WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $guns->set($row['wot_items_guns_id'], new Gun($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_items_guns_turrets.*
			FROM wot_items_guns_turrets
			JOIN wot_items_guns ON wot_items_guns.wot_items_guns_id = wot_items_guns_turrets.wot_items_guns_id
			WHERE wot_version_id = $version_id
		");

		while(($row = $query->fetch_array())) {
			$item = new Turret\Gun($row, true);
			$item->update(array(
				'wot_items_guns_id' => $guns->get($row['wot_items_guns_id'])->getRelator(),
				'wot_items_turrets_id' => $turrets->get($row['wot_items_turrets_id'])->getRelator(),
			));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_shells WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $shells->set($row['wot_items_shells_id'], new Shell($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_items_shells_guns.*
			FROM wot_items_shells_guns
			JOIN wot_items_guns ON wot_items_guns.wot_items_guns_id = wot_items_shells_guns.wot_items_guns_id
			WHERE wot_version_id = $version_id
		");

		while(($row = $query->fetch_array())) {
			$item = new Gun\Shell($row, true);
			$item->update(array(
				'wot_items_guns_id' => $guns->get($row['wot_items_guns_id'])->getRelator(),
				'wot_items_shells_id' => $shells->get($row['wot_items_shells_id'])->getRelator()
			));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_engines WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $engines->set($row['wot_items_engines_id'], new Engine($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_items_engines_tanks.*
			FROM wot_items_engines_tanks
			JOIN wot_items_engines ON wot_items_engines.wot_items_engines_id = wot_items_engines_tanks.wot_items_engines_id
			WHERE wot_version_id = $version_id
		");
		while(($row = $query->fetch_array())) {
			$item = new Tank\Engine($row, true);
			$item->update(array(
				'wot_tanks_id' => $tanks->get($row['wot_tanks_id'])->getRelator(),
				'wot_items_engines_id' => $engines->get($row['wot_items_engines_id'])->getRelator()
			));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_items_radios WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $radios->set($row['wot_items_radios_id'], new Radio($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_items_radios_tanks.*
			FROM wot_items_radios_tanks
			JOIN wot_items_radios ON wot_items_radios_tanks.wot_items_radios_id = wot_items_radios.wot_items_radios_id
			WHERE wot_version_id = $version_id
		");
		while(($row = $query->fetch_array())) {
			$item = new Tank\Radio($row, true);
			$item->update(array(
				'wot_tanks_id' => $tanks->get($row['wot_tanks_id'])->getRelator(),
				'wot_items_radios_id' => $radios->get($row['wot_items_radios_id'])->getRelator()
			));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_tanks_parents.*
			FROM wot_tanks_parents
			JOIN wot_tanks ON wot_tanks_parents.wot_tanks_id = wot_tanks.wot_tanks_id
			WHERE wot_version_id = $version_id
		");
		while(($row = $query->fetch_array())) {
			$item = new Tank\Parentus($row, true);
			$item->update(array(
				'wot_tanks_id' => $tanks->get($row['wot_tanks_id'])->getRelator(),
				'parent_id' =>  $tanks->get($row['parent_id'])->getRelator(),
			));
			$this->setItem($item);
		}

		$query = $this->db->query("SELECT * FROM wot_equipment WHERE wot_version_id = $version_id");
		while(($row = $query->fetch_array())) {
			$item = $equipment->set($row['wot_equipment_id'], new Equipment($row, true));
			$this->setItem($item);
		}

		$query = $this->db->query("
			SELECT wot_equipment_params.*
			FROM wot_equipment_params
			JOIN wot_equipment ON wot_equipment_params.wot_equipment_id = wot_equipment.wot_equipment_id
			WHERE wot_version_id = $version_id
		");
		while(($row = $query->fetch_array())) {
			$item = new Equipment\Param($row, true);
			$item->update(array(
				'wot_equipment_id' => $equipment->get($row['wot_equipment_id'])->getRelator()
			));
			$this->setItem($item);
		}
	}

	public function save() {

		function dd($table, $keys = array()) {
			if (count($keys) == 0)
				$keys = array("wot_{$table}_id");
			return array('table' => "wot_$table", 'key' => "wot_{$table}_id", 'keys' => $keys);
		}

		$map = array(
			'fuel_tank' => dd('items_tanks'),
			'tank' => dd('tanks'),
			'engine' => dd('items_engines'),
			'chassis' => dd('items_chassis'),
			'turret' => dd('items_turrets'),
			'gun' => dd('items_guns'),
			'shell' => dd('items_shells'),
			'radio' => dd('items_radios'),
			'turret_gun' => dd('items_guns_turrets', array('wot_items_turrets_id', 'wot_items_guns_id')),
			'gun_shell' => dd('items_shells_guns', array('wot_items_shells_id', 'wot_items_guns_id')),
			'tank_engine' => dd('items_engines_tanks', array('wot_tanks_id', 'wot_items_engines_id')),
			'tank_radio' => dd('items_radios_tanks', array('wot_tanks_id', 'wot_items_radios_id')),
			'tank_parent' => dd('tanks_parents'),
			'equipment' => dd('equipment'),
			'equipment_param' => dd('equipment_params')
		);

		try {
			foreach($map as $type => $info) {

				$this->logger->log(LogLevel::INFO, "Saving $type.");

				/** @var Item $item */
				foreach($this->types[$type] as $item) {

					$values = array();

					foreach($item->getValues() as $key => $value) {
						if ($value instanceof Relator) {

							$related = null;

							/** @var Item $subItem */
							foreach($this->types[$value->getType()] as $subItem) {
								if ($subItem->match($value->getValues())) {
									$related = $subItem;
									break;
								}
							}

							if (!$related) {
								throw new \Exception("Failed to resolve relation.");
							}

							$values[$key] = $related->get($map[$value->getType()]['key']);
							if (!$values[$key]) {
								$details = "Related item ($key) is not saved in DB!\r\n";
								$details .= print_r($related, true);
								$details .= "For item:\r\n";
								$details .= print_r($item, true);

								throw new \Exception("Related item not saved. Details: $details");
							}

						} else {
							$values[$key] = $value;
						}
					}

					foreach($values as $key => $value) {
						if (is_array($value)) {
							$details = "Wrong value for key $key (in type $type)\r\n";

							foreach($value as $k => $v) {
								if (is_object($v)) {
									$details .= "$k: " . get_class($v) . "\r\n";
								} else {
									$details .= "$k: $v\r\n";
								}
							}

							$details .= "\r\n";

                            trigger_error($details, E_USER_WARNING);
						}
					}

					if (!$item->isSaved()) {
						$this->db->insertCached($info['table'], $values);
						$item->update(array(
							$info['key'] => $this->db->insertId()
						));
					} else {

						$conditions = array();
						$conditions_params = array();
						foreach($item->getKeys() as $key) {
							$conditions[] = "$key = ?";
							$conditions_params[] = $values[$key];
							unset($values[$key]);
						}

						unset($values[$info['key']]);

						if (count($values) > 0) {
							$this->db->updateCached($info['table'], array(null, implode(' AND ', $conditions), $conditions_params), $values);
						}
					}
				}
			}

			$this->db->commit();

		} catch(\Exception $e) {
			$this->db->rollback();
			throw $e;
		}
	}
}