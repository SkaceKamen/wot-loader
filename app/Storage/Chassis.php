<?php
namespace Loader\Storage;

class Chassis extends Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('chassis', array('name_node', 'wot_version_id'), $values, $saved);
	}
}