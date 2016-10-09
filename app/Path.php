<?php
namespace Loader;

use \Loader\Xml\Decompressor;

class Path
{
	private $path;

	const PATH_MESSAGES = '/res/text/LC_MESSAGES/';
	const PATH_VEHICLES = '/res/scripts/item_defs/vehicles/';

	public function __construct($path) {
		$this->path = $path;
	}

	public function getVersion() {
		return (new Version\Reader($this))->get();
	}

	public function exists() {
		return is_dir($this->path);
	}

	public function getPath() {
		return $this->path;
	}

	public function getTranslationsPath() {
		return $this->path . self::PATH_MESSAGES;
	}

	public function extractItems($target_path) {
		if (!file_exists($target_path))
			mkdir($target_path);

		Decompressor::init();

		$this->extractItemsPath($this->getPath() . self::PATH_VEHICLES, $target_path);
	}

	private function extractItemsPath($current, $target, $base = null) {
		if (is_null($base)) {
			$base = $current;
		}

		$handle = opendir($current);

		while (($file = readdir($handle)) !== false) {
			if ($file == '.' || $file == '..')
				continue;

			$file = $current . '/' . $file;
			if (is_dir($file)) {
				$this->extractItemsPath($file, $target, $base);
			} else {
				$path_target = $target . substr($current, strlen($base)) . '/';

				if (!file_exists($path_target)) {
					mkdir($path_target, 777, true);
				}

				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$name = pathinfo($file, PATHINFO_BASENAME);
				if ($ext == 'xml') {
					Decompressor::decodePackedFile($file, $name, $path_target . $name);
				}
			}
		}
	}

	public function extractTranslations($target_path) {
		if (!file_exists($target_path))
			mkdir($target_path);

		$folder = $this->getTranslationsPath();
		$folder_handle = opendir($folder);

		while (($file = readdir($folder_handle)) !== false) {
			if ($file == '.' || $file == '..' || is_dir($folder . $file))
				continue;

			$name = pathinfo($file, PATHINFO_FILENAME);
			if (substr($name, strlen($name) - 8) == 'vehicles' || $name == 'artefacts') {
				copy($folder . $file, $target_path . '/' . $file);
			}
		}
	}

}