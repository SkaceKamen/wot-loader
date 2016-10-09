<?php
namespace Loader\Xml;

class DataDescriptor
{
	public $address;
	public $end;
	public $type;

	public function __construct($end, $type, $address) {
		$this->end = $end;
		$this->type = $type;
		$this->address = $address;
	}

	public function __toString() {
		$str = '[';
		$str .= '0x';
		$str .= dechex($this->end);
		$str .= ', ';
		$str .= '0x';
		$str .= dechex($this->type);
		$str .= ']@0x';
		$str .= dechex($this->address);
		return $str;
	}
}