<?php
namespace Loader;

use Loader\Logger\Text;
use Psr\Log;

class Reader implements Log\LoggerAwareInterface
{
    /** @var Version\Reader $version */
	private $version;

    /** @var Translations\Reader $translations */
    private $translations;

	/** @var Config\Reader $config */
	private $config;

	/** @var Log\LoggerInterface $logger */
	private $logger;

	/** @var Mysqler $db */
	private $db;

	/** @var Path $path */
	private $path;

	/** @var ErrorHandler $errorHandler */
	private $errorHandler;

	/**
	 * Reader constructor.
	 * @param Config\Reader $config
	 */
	public function __construct($config) {
		$this->config = $config;

		$this->errorHandler = new ErrorHandler();
		$this->errorHandler->hook();

		$this->setLogger(new Text());

		$this->db = new Mysqler(
			$config->mysql->hostname,
			$config->mysql->username,
			$config->mysql->password,
			$config->mysql->database
		);
		
		$this->path = new Path($config->paths->game);
	}

	private function log($text, $level=Log\LogLevel::INFO) {
		if ($this->logger)
			$this->logger->log($level, $text);
	}
	
	public function run($params) {
		$this->checkVersion(isset($params['version']) ? $params['version'] : null);
		$this->loadTranslations();
		
		$reader = new Extracted\Reader(
			$this->getItemsPath(),
			$this->version['id'],
			$this->translations
		);
		
		$this->log("Loading current storage state...");
		
		$storage = new Storage\Mysql($this->db);
		$storage->preload($this->version['id']);
		
		$this->log("Current storage loaded.");

		$this->log("Reading game data.");

		$items = $reader->getEquipment();
		foreach($items as $item) {
			$storage->setItem($item);
		}
		
		foreach($reader->getNations() as $nation) {
			$this->log("Loading $nation");
			
			$items = $reader->getItems($nation);
			foreach($items as $item) {
				$storage->setItem($item);
			}
		}
		
		$this->log("Done reading game data.");

		$this->log("Saving updated storage");
		
		$storage->save();
		
		$this->log("Done saving.");
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

	/**
	 * Sets a logger instance on the object.
	 *
	 * @param Log\LoggerInterface $logger
	 *
	 * @return null
	 */
	public function setLogger(Log\LoggerInterface $logger) {
		$this->logger = $logger;
		$this->errorHandler->setLogger($logger);
	}
}