<?php
namespace Loader\Extracted\Reader;

use Loader\Path;
use Loader\Storage\Item;

class Items
{
	protected $idsSeparated = false;
	protected $map = array();

    /** @var \Loader\Extracted\Reader $reader */
    protected $reader = null;
	protected $cls = '\Loader\Storage\Item';
	protected $file;
	
	private $translated = array(
		'userString'
	);
	
	public function __construct($file, $cls, $map, $idsSeparated=true) {
		$this->file = $file;
		$this->cls = $cls;
		$this->map = $map;
		$this->idsSeparated = $idsSeparated;
	}
	
	public function setReader($reader) {
		$this->reader = $reader;
	}
	
	public function getTranslator() {
		return $this->reader->getTranslator();
	}

	/**
	 * @param Path $path
	 * @param string $nation
	 * @param int $version
	 * @return Item[]
	 * @throws \Exception
	 */
	public function read($path, $nation, $version) {
		$items = array();
		$ids = array();
		$items_list = null;
		
		$path = $path . '/' . $nation . '/' . $this->file;
		$list = simplexml_load_file($path);

		if ($this->idsSeparated) {
			$items_ids = $list->ids->children();
			foreach($items_ids as $node => $content) {
				$ids[strtolower($nation . '-' . $node)] = (string)$content;
			}
	 
			$items_list = $list->shared->children();
			
			if (count($items_list) == 0) {
				foreach($ids as $node => $id) {
					$items[] = new $this->cls(array(
						'wot_version_id' => $version,
						'id' => $id,
						'name_node' => $node
					));
				}
				
				return $items;
			}
			
		} else {
			$items_list = $list->children();
		}
		
		foreach($items_list as $node => $content) {
			if ($node == 'icons')
				continue;
			
			$node = strtolower($nation . '-' . $node);
			$data = array(
				'wot_version_id' => $version,
				'id' => isset($content->id) ? (string)$content->id : $ids[$node],
				'name_node' => $node
			);
			
			if (!$this->idsSeparated && !isset($content->id)) {
				throw new \Exception("Component $node (ids inside details) doesn't have id.");
			}
			
			if ($this->idsSeparated && !isset($ids[$node])) {
				throw new \Exception("Component $node (ids outside details) doesn't have id.");
			}
			
			foreach($this->map as $key => $value) {
				if ($key === 'nation') {
					$data[$key] = $nation;
					continue;
				}
				
				if (is_callable($value)) {
					$data[$key] = call_user_func_array($value, array($content, $key));
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
			
			$items[] = $item;
		}
		
		return $items;
	}
}