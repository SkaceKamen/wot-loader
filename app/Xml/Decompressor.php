<?php
namespace Loader\Xml;

class Decompressor
{
	/** @var PackedSection $PS */
	public static $PS;
	public static $failed = array();

	public static function init() {
		static::$PS = new PackedSection();
	}

	public static function decodePackedFile($filename, $name, $target, $size = null) {
		try {
			$reader = new ByteReader($filename, $size);
			$head = $reader->readInt32();
			if ($head == PackedSection::$Packet_Header) {
				$reader->readSByte();
				$list = static::$PS->readDictionary($reader);
				$xmlNode = new \SimpleXMLElement("<{$name}></{$name}>");
				static::$PS->readElement($reader, $xmlNode, $list);
				$reader->close();
				file_put_contents($target, $xmlNode->asXML());
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			static::$failed[] = array(
				'filename' => $filename,
				'exception' => $e
			);
			return false;
		}
	}
}