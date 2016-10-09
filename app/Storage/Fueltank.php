<?php
namespace Loader\Storage;

class Fueltank extends Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('fuel_tank', array('name_node', 'nation', 'wot_version_id'), $values, $saved);
	}
}
