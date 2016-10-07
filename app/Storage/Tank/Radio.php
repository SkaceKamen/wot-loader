<?php
namespace Loader\Storage\Tank;

class Radio extends \Loader\Storage\Item {
	public function __construct($values, $saved=false) {
		parent::__construct('tank_radio', array('wot_tanks_id', 'wot_items_radios_id'), $values, $saved);
	}
}