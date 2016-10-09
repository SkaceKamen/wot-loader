<?php
namespace Loader\Xml;

class ByteReader
{
	public static $instance;

	private $handle;
	private $file;
	private $size;

	public function __construct($file) {
		static::$instance = $this;

		$this->file = $file;
		$this->size = filesize($file);
		$this->handle = fopen($file, 'rb');
	}

	public function readByte() {
		return ord(fread($this->handle, 1));
	}

	public function readSByte() {
		$result = $this->readByte();
		return $result;
	}

	public function readBytes($length) {
		$bytes = array();
		for ($i = 0; $i < $length; $i++)
			$bytes[$i] = $this->readByte();
		return $bytes;
	}

	public function readSBytes($length) {
		$bytes = array();
		for ($i = 0; $i < $length; $i++)
			$bytes[$i] = $this->readSByte();
		return $bytes;
	}

	public function readChar() {
		return fread($this->handle, 1);
	}

	public function readChars($length) {
		if ($length <= 0)
			return "";
		if ($this->position() + $length > $this->size)
			throw new \Exception("Position + length is out of file");
		return fread($this->handle, $length);
	}

	public function readInt32() {
		$result = unpack('l', $this->readChars(4));
		return $result[1];
	}

	public function readInt16() {
		$result = unpack('v', $this->readChars(2));
		return $result[1];
	}

	public function readSingle() {
		$result = unpack('f', $this->readChars(4));
		return $result[1];
	}

	public function position() {
		return ftell($this->handle);
	}
}