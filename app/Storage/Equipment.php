<?php
namespace Loader\Storage;

class Equipment extends \Loader\Storage\Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('equipment', array('name_node', 'wot_version_id'), $values, $saved);
	}
}