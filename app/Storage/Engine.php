<?php
namespace Loader\Storage;

class Engine extends Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('engine', array('name_node', 'wot_version_id'), $values, $saved);
	}
}