<?php
namespace Loader\Storage\Tank;

class Engine extends \Loader\Storage\Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('tank_engine', array('wot_tanks_id', 'wot_items_engines_id'), $values, $saved);
	}
}