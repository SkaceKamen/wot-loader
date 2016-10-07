<?php
namespace Loader\Storage;

class Radio extends Item {
	public function __construct($values, $saved=false) {
		parent::__construct('radio', array('name_node', 'wot_version_id'), $values, $saved);
	}
}