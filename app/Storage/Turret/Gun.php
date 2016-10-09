<?php
namespace Loader\Storage\Turret;

class Gun extends \Loader\Storage\Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('turret_gun', array('wot_items_guns_id', 'wot_items_turrets_id'), $values, $saved);
	}
}