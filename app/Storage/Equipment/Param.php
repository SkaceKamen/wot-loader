<?php
namespace Loader\Storage\Equipment;

class Param extends \Loader\Storage\Item {
	public function __construct($values, $saved=false) {
		parent::__construct('equipment_param', array('wot_equipment_id', 'param'), $values, $saved);
	}
}