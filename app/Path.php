<?php
namespace Loader;

use \Loader\Xml\Decompressor;

class Path
{
	private $path;

	const PATH_MESSAGES = '/res/text/LC_MESSAGES/';
	const PATH_SCRIPTS = '/res/packages/scripts.pkg';
	const PATH_VEHICLES = 'scripts/item_defs/vehicles/';

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

		$this->extractArchivePath($this->getPath() . self::PATH_SCRIPTS, $target_path);
	}

	private function extractArchivePath($archive, $target) {
		// Open archive
		$zip = new \ZipArchive();
		if ($zip->open($archive) !== true) {
			throw new \Exception("Failed to open archive '$archive'.");
		}

		// Extract relevant files
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$file = $zip->getNameIndex($i);
			$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

			if ($ext == 'xml' && strpos($file, self::PATH_VEHICLES) === 0) {
				$target_filename = $target . '/' . substr($file, strlen(self::PATH_VEHICLES));
				$target_dir = dirname($target_filename);

				if (!file_exists($target_dir)) {
					mkdir($target_dir, 777, true);
				}

				// @TODO: This is dirty, jesus
				// $zip->extractTo($target, array($file));
				copy("zip://{$archive}#{$file}", $target_filename);

				Decompressor::decodePackedFile(
					$target_filename,
					pathinfo($file, PATHINFO_BASENAME),
					$target_filename
				);
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