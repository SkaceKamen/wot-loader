<?php
namespace Loader\Storage\Gun;

class Shell extends \Loader\Storage\Item
{
	public function __construct($values, $saved = false) {
		parent::__construct('gun_shell', array('wot_items_guns_id', 'wot_items_shells_id'), $values, $saved);
	}
}