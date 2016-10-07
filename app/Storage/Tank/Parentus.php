<?php
namespace Loader\Storage\Tank;

class Parentus extends \Loader\Storage\Item {
	public function __construct($values, $saved=false) {
		parent::__construct('tank_parent', array('wot_tanks_id', 'parent_id'), $values, $saved);
	}
}