<?php
namespace Loader\Storage;

class Tank extends Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('tank', array('name_node', 'wot_version_id'), $values, $saved);
	}
}