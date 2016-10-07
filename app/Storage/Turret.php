<?php
namespace Loader\Storage;

class Turret extends Item {
	public function __construct($values, $saved=false) {
		parent::__construct('turret', array('name_node', 'wot_version_id'), $values, $saved);
	}
}