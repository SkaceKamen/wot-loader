<?php
namespace Loader;

class Reader
{
    /**
     * @var Version\Reader $version
     */
	private $version;

    /**
     * @var Translations\Reader $translations
     */
    private $translations;
	
	public function __construct($config) {
		$this->config = $config;
		
		$this->db = new mysqler(
			$config->mysql->hostname,
			$config->mysql->username,
			$config->mysql->password,
			$config->mysql->database
		);
		
		$this->path = new Path($config->paths->game);
	}
	
	public function run($params) {
		$this->checkVersion(isset($params['version']) ? $params['version'] : null);
		$this->loadTranslations();
		
		$reader = new Extracted\Reader(
			$this->getItemsPath(),
			$this->version['id'],
			$this->translations
		);
		
		echo "Preloading...\r\n";
		
		$storage = new Storage\Mysql($this->db);
		$storage->preload($this->version['id']);
		
		echo "Done preloading.\r\n";
		
		$items = $reader->getEquipment();
		foreach($items as $item) {
			$storage->setItem($item);
		}
		
		foreach($reader->getNations() as $nation) {
			echo "Loading $nation...\r\n";
			
			$items = $reader->getItems($nation);
			foreach($items as $item) {
				$storage->setItem($item);
			}
		}
		
		echo "Done loading.\r\n";
		echo "Saving to DB...\r\n";
		
		$storage->save();
		
		echo "Done saving.\r\n";
	}
	
	private function getItemsPath() {
		return $this->config->paths->versions . '/' . $this->version['version'];
	}
	
	private function getTranslationsPath() {
		return $this->config->paths->texts . '/' . $this->version['version'];
	}
	
	private function checkVersion($version = null) {
		if ($version == null) {
			$version = $this->path->getVersion();
		}
		
		$version = $this->db->escape($version);
		
		$exists = $this->db->query("SELECT id, version FROM wot_versions WHERE version = '{$version}' OR id = '{$version}'");
		$exists = $this->db->row($exists);
		
		if ($exists) {
			$this->version = array(
				'id' => $exists['id'],
				'version' => $exists['version']
			);
		} else {
			$this->createVersion($version);
		}
	}
	
	private function createVersion($version) {
		$this->db->insert('wot_versions', array('version' => $version, 'published' => time()));
		$this->version = array(
			'id' => $this->db->insertId(),
			'version' => $version
		);
		
		if (file_exists($this->getItemsPath())) {
			$this->path->extractItems($this->getItemsPath());
			$this->path->extractTranslations($this->getTranslationsPath());
		}
	}
	
	private function loadTranslations() {
		$this->translations = new Translations\Reader($this->getTranslationsPath());
	}
	
	public function _($key) {
		return $this->translations->get($key);
	}
}