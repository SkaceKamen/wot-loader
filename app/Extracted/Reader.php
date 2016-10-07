<?php
namespace Loader\Extracted;

class Reader
{
	private $path;
	private $versionId;
	private $translator;
	
	public function __construct($path, $version_id, $translator) {
		$this->path = $path;
		$this->versionId = $version_id;
		$this->translator = $translator;
	}
	
	public function getNations() {
		$nations = array();
		$dir = opendir($this->path);
		while($nation = readdir($dir)) {
			if ($nation!='.' && $nation!='..' && $nation != 'common') {
				$nations[] = $nation;
			}
		}
		return $nations;
	}
	
	public function getItems($nation) {
		
		$items = array();
		$items = array_merge($items, $this->getRadios($nation));
		$items = array_merge($items, $this->getEngines($nation));
		$items = array_merge($items, $this->getTurrets($nation));
		$items = array_merge($items, $this->getGuns($nation));
		$items = array_merge($items, $this->getShells($nation));
		$items = array_merge($items, $this->getEngines($nation));
		$items = array_merge($items, $this->getChassis($nation));
		$items = array_merge($items, $this->getFuelTanks($nation));
		$items = array_merge($items, $this->getTanks($nation));
		
		return $items;
		
	}
	
	public function getTranslator() {
		return $this->translator;
	}
	
	public function getEquipment() {
		return $this->getReader(new Reader\Equipment(), null);
	}
	
	public function getRadios($nation) {		
		return $this->getReader(new Reader\Radios(), $nation);
	}
	
	public function getEngines($nation) {
		return $this->getReader(new Reader\Engines(), $nation);
	}
	
	public function getShells($nation) {
		return $this->getReader(new Reader\Shells(), $nation);
	}
	
	public function getGuns($nation) {
		return $this->getReader(new Reader\Guns(), $nation);
	}
	
	public function getTanks($nation) {
		return $this->getReader(new Reader\Tanks(), $nation);
	}
	
	public function getFuelTanks($nation) {
		return $this->getReader(new Reader\Fueltanks(), $nation);
	}
	
	public function getChassis($nation) {
		return $this->getReader(new Reader\Chassis(), $nation);
	}
	
	public function getTurrets($nation) {
		return $this->getReader(new Reader\Turrets(), $nation);
	}

    /**
     * @param Reader\Item $reader
     * @param string $nation
     * @return mixed
     */
	private function getReader($reader, $nation) {
		return $reader->setReader($this)->read($this->path, $nation, $this->versionId);
	}
}