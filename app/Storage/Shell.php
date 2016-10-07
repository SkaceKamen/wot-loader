<?php
namespace Loader\Storage;

class Shell extends Item {
	public function __construct($values, $saved=false) {
		parent::__construct('shell', array('name_node', 'wot_version_id'), $values, $saved);
	}
}