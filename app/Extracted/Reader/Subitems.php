<?php
namespace Loader\Extracted\Reader;

use Loader\Storage\Relator;
use Loader\Storage\Tank\Parentus;

class Subitems
{
	protected $map = array();
	protected $preset = array();
	protected $cls = '\Loader\Storage\Item';

    /** @var \Loader\Extracted\Reader $reader */
	protected $reader = null;
	protected $includeNode = true;
	protected $unlocksParent = null;
	
	private $translated = array(
		'userString'
	);
	
	public function __construct($reader, $cls, $preset, $map, $include_node = true, $unlocks_parent = null) {
		$this->reader = $reader;
		$this->cls = $cls;
		$this->map = $map;
		$this->preset = $preset;
		$this->includeNode = $include_node;
		$this->unlocksParent = $unlocks_parent;
	}
	
	public function getTranslator() {
		return $this->reader->getTranslator();
	}
	
	public function read($element, $nation) {
		$items = array();
		
		if (!$element)
			throw new \Exception("Undefined element passed to subitems reader.");
		
		$items_list = $element->children();		
		foreach($items_list as $node => $content) {
			if ($node == 'icons')
				continue;
			
			$node = strtolower($nation . '-' . $node);
			$data = $this->preset;
			
			if ($this->includeNode)
				$data['name_node'] = $node;
			
			foreach($this->map as $key => $value) {
				if ($key === 'nation') {
					$data[$key] = $nation;
					continue;
				}

				if (is_callable($value)) {
					$data[$key] = call_user_func_array($value, array($content, $key));
				} else if ($value instanceof This) {
					$data[$key] = new Relator($value->getType(), array(
						'name_node' => $node,
						'wot_version_id' => $value->getVersion()
					));
				} else {
					
					if (!is_array($value) && in_array($value, $this->translated)) {
						$data[$key] = $this->getTranslator()->get((string)$content->$value);
					} else {
						if (is_array($value)) {
							$current = $content;
							foreach($value as $subkey) {
								if (!is_object($current) || !isset($current->$subkey)) {
									$current = null;
									break;
								}
								$current = $current->$subkey;
							}
							
							if ($current !== null)
								$data[$key] = (string)$current;
						} else {
							if (isset($content->$value))
								$data[$key] = (string)$content->$value;
						}
					}
				}
			}
			
			$item = new $this->cls($data);
			$item->setRaw($content);
			
			if ($this->unlocksParent && isset($content->unlocks)) {
				
				foreach($content->unlocks->children() as $key => $value) {
					if ($key == 'vehicle') {
						if (!is_array($value)) {
							$value = array($value);
						}
						
						foreach($value as $unlock) {
							$cost = (string)$unlock->cost;
							$name_node = "$nation-$unlock";
							
							$items[] = new Parentus(array(
								'wot_tanks_id' => new Relator('tank', array(
									'name_node' => $name_node,
									'wot_version_id' => $this->unlocksParent->get('wot_version_id')
								)),
								'parent_id' => $this->unlocksParent->getRelator()
							));
						}
					}
				}
				
			}
			
			$items[] = $item;
		}
		
		return $items;
	}
}