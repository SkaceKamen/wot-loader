<?php
namespace Loader\Storage;

class Gun extends Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('gun', array('name_node', 'wot_version_id'), $values, $saved);
	}
}