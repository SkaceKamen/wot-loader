<?php
namespace Loader\Version;

class Reader
{
	/** @var \Loader\Path $wot */
	private $wot;

    /**
     * Reader constructor.
     * @param \Loader\Path $wot
     */
	public function __construct($wot) {
		$this->wot = $wot;
	}
	
	public function getPath() {
		return $this->wot->getPath() . '/version.xml';
	}
	
	public function get() {
		$version_path = $this->getPath();
		
		if (!file_exists($version_path))
			throw new \Exception("File '$version_path' doesn't exists.");
		
		$version = simplexml_load_file($version_path);
		
		return trim((string)$version->version);
	}
	
}