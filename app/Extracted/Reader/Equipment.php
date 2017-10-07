<?php
namespace Loader\Extracted\Reader;

use Loader\Storage;

class Equipment extends Item
{
	public function read($path, $nation, $version) {
		$items = array();

		$equipment_list = simplexml_load_file($path . '/common/optional_devices.xml');
		foreach ($equipment_list->children() as $node => $item) {
			$icon = (string)$item->icon;
			$icon = explode(' ', $icon);
			$icon = $icon[0];
			$icon = str_replace('../maps/icons/artefact/', '', $icon);

			$price = 0;
			$price_gold = 0;
			if (isset($item->price->gold))
				$price_gold = (int)$item->price;
			else
				$price = (int)$item->price;

			$weight = 0;
			if (isset($item->script->weight))
				$weight = (int)$item->script->weight;

			$include = '';
			$exclude = '';

			$inc = 'include';
			if (isset($item->vehicleFilter->$inc->vehicle->tags))
				$include = (string)$item->vehicleFilter->$inc->vehicle->tags;
			if (isset($item->vehicleFilter->exclude->vehicle->tags))
				$exclude = (string)$item->vehicleFilter->exclude->vehicle->tags;

			//remove tabs
			$include = preg_replace('/\s+/', ' ', $include);
			$exclude = preg_replace('/\s+/', ' ', $exclude);

			$equipment = new Storage\Equipment(array(
				'wot_version_id' => $version,
				'name' => $this->translate((string)$item->userString),
				'name_node' => $node,
				'description' => $this->translate((string)$item->description),
				'icon' => $icon,
				'price' => $price,
				'price_gold' => $price_gold,
				'removable' => (((string)$item->removable) === 'true') ? 1 : 0,
				'weight' => $weight,
				'vehicle_tags_include' => $include,
				'vehicle_tags_exclude' => $exclude
			));

			$items[] = $equipment;

			$break = false;
			foreach ($item->script->children() as $param_node => $param_value) {
				if ($param_node == 'weight')
					continue;
				if ($param_node == 'attribute' || $param_node == 'value' || $param_node == 'factor') {
					$data = array(
						'wot_equipment_id' => $equipment->getRelator(),
						'param' => (string)$item->script->attribute,
						'value' => isset($item->script->value) ? (string)$item->script->value : (string)$item->script->factor
					);
					$break = true;
				} else {
					$data = array(
						'wot_equipment_id' => $equipment->getRelator(),
						'param' => (string)$param_node,
						'value' => (string)$param_value
					);
				}
				if (strpos($data['param'], '/'))
					$data['param'] = substr($data['param'], strpos($data['param'], '/') + 1);

				$items[] = new Storage\Equipment\Param($data);

				if ($break)
					break;
			}
		}

		return $items;
	}
}