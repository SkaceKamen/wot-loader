<?php
namespace Loader\Extracted\Reader;

class Item
{
	protected $itemsReader = null;
	protected $reader = null;
	
	public function setReader($reader) {
		$this->reader = $reader;
		if ($this->itemsReader)
			$this->itemsReader->setReader($this->reader);
		return $this;
	}
	
	public function read($path, $nation, $version) {
		throw new Exception("This should be implemented.");
	}
	
	public function translate($value) {
		return $this->reader->getTranslator()->get($value);
	}
	
	public function getPitchLimits($element) {
		if (!isset($element->pitchLimits))
			return null;
		
		$element = $element->pitchLimits;
		$min = isset($element->minPitch) ? (string)$element->minPitch : "";
		$max = isset($element->maxPitch) ? (string)$element->maxPitch : "";
		
		return "$min, $max";
	}
	
	public function getArmor($list) {
		if (!$list || $list->count() == 0)
			return array();
		
		$armor = array();
		foreach($list->children() as $node=>$content) {
			$node = substr($node, strpos($node,'_') + 1, strlen($node));
			$armor[$node] = (int)$content;
			if ((bool)@$content->noDamage) {
				$armor[$node] .= '(true)';
			}
		}
		return $armor;
	}
	
	public function getArmorString($list) {
		return implode(' ', $this->getArmor($list->armor));
	}
	
	public function getPrimaryArmor($list, $armor, $tank=null) {
		if (count($armor) == 0)
			return "";
		
		$armor_primary = '';
		foreach(explode(' ', $list) as $node) {
			$node = substr($node, strpos($node,'_')+1,strlen($node));
			if (isset($armor[$node])) {
				$armor_primary .= $armor[$node] . ' ';
			} else {
				echo "<b>Warning:</b> $tank doesn't have primary armor node.\r\n";
				$armor_primary .= "? ";
			}
		}
		
		return trim($armor_primary); 
	}
	
	public function getCrew($element) {
		$crew = array();
		$elements = $element->crew->children();
		foreach($elements as $node => $value) {
			if (!strlen($value)) {
				$crew[] = $node;
			} else {
				$value = str_replace(array("\r","\t",""), "", $value);
				$value = str_replace("\n", " ", $value);
				$crew[] = "$node ($value)";
			}
		}
		
		return implode(', ', $crew);
	}
	
	public function getPrimaryArmorString($list) {
		return $this->getPrimaryArmor((string)$list->primaryArmor, $this->getArmor($list->armor), $list->getName());
	}
	
}