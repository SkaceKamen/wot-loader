<?php
namespace Loader\Storage;

class Fueltank extends StorageItem {
	public function __construct($values, $saved=false) {
		parent::__construct('fuel_tank', array('name_node', 'nation', 'wot_version_id'), $values, $saved);
	}
}
