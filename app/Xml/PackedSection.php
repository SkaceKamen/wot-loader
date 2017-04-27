<?php
namespace Loader\Xml;

class PackedSection
{
	const MAX_LENGTH = 256;

	public static $intToBase64 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '/');
	public static $Packet_Header = 1654738501;

	public function readData(ByteReader $reader, $dictionary, &$element, $offset, DataDescriptor $dataDescriptor) {
		$i = $dataDescriptor->end - $offset;
		if ($dataDescriptor->type == 0) {
			$this->readElement($reader, $element, $dictionary);
		} else if ($dataDescriptor->type == 1) {
			$element->{0} = $this->readString($reader, $i);
		} else if ($dataDescriptor->type == 2) {
			$element->{0} = $this->readNumber($reader, $i);
		} else if ($dataDescriptor->type == 3) {
			$s = $this->readFloats($reader, $i);
			$chArr = array(' ');
			$sArr1 = explode(' ', $s);
			if (count($sArr1) == 12) {
				$element->addChild('row0', "{$sArr1[0]} {$sArr1[1]} {$sArr1[2]}");
				$element->addChild('row1', "{$sArr1[3]} {$sArr1[4]} {$sArr1[5]}");
				$element->addChild('row2', "{$sArr1[6]} {$sArr1[7]} {$sArr1[8]}");
				$element->addChild('row3', "{$sArr1[9]} {$sArr1[10]} {$sArr1[11]}");
			} else {
				$element->{0} = "\t{$s}\t";
			}
		} else if ($dataDescriptor->type == 4) {
			if ($this->readBoolean($reader, $i))
				$element->{0} = 'true';
			else
				$element->{0} = 'false';
		} else if ($dataDescriptor->type == 5) {
			$element->{0} = $this->readBase64($reader, $i);
		} else {
			throw new \Exception("Unknown type of {$element->getName()}: {$dataDescriptor} " . $this->readAndToHex($reader, $i));
		}
		return $dataDescriptor->end;
	}

	public function readDataDescriptor(ByteReader $reader) {
		$i = $this->readLittleEndianInt($reader);
		return new DataDescriptor($i & 268435455, $i >> 28, $reader->position());
	}

	public function readDictionary(ByteReader $reader) {
		$list = array();
		$i = 0;
		$s = $this->readStringTillZero($reader);
		while (strlen($s) != 0) {
			$list[] = $s;
			$s = $this->readStringTillZero($reader);
			$i++;
		}
		return $list;
	}

	public function isNormal($text) {
		if ($text == "N�b")
			return false;
		return true;
	}

	public function readElement(ByteReader $reader, &$element, $dictionary) {
		$i1 = $this->readLittleEndianShort($reader);
		$dataDescriptor = $this->readDataDescriptor($reader);
		$elementDescriptorArr1 = $this->readElementDescriptors($reader, $i1);
		$i2 = $this->readData($reader, $dictionary, $element, 0, $dataDescriptor);
		$elementDescriptorArr2 = $elementDescriptorArr1;
		for ($i3 = 0; $i3 < count($elementDescriptorArr2); $i3++) {
			$elementDescriptor = $elementDescriptorArr2[$i3];
			$tag = $dictionary[$elementDescriptor->nameIndex];
			$tag = str_replace(":", "_", $tag);
			$xmlNode = new \SimpleXMLElement("<{$tag}></{$tag}>");
			$i2 = $this->readData($reader, $dictionary, $xmlNode, $i2, $elementDescriptor->dataDescriptor);
			$this->appendSimpleXml($element, $xmlNode);
		}
	}

	public function readElementDescriptors(ByteReader $reader, $number) {
		$elementDescriptorArr = array();
		for ($i1 = 0; $i1 < $number; $i1++) {
			$i2 = $this->readLittleEndianShort($reader);
			$dataDescriptor = $this->readDataDescriptor($reader);
			$elementDescriptorArr[$i1] = new ElementDescriptor($i2, $dataDescriptor);
		}
		return $elementDescriptorArr;
	}

	public function readFloats(ByteReader $reader, $lengthInBytes) {
		$i1 = $lengthInBytes / 4;
		$str = '';
		for ($i2 = 0; $i2 < $i1; $i2++) {
			if ($i2 != 0)
				$str .= ' ';
			$f = $this->readLittleEndianFloat($reader);
			$str .= number_format($f, 6, '.', '');
		}
		return $str;
	}

	public function readAndToHex(ByteReader $reader, $lengthInBytes) {
		$sbArr1 = $reader->readSBytes($lengthInBytes);
		$str = '[ ';
		$sbArr2 = $sbArr1;
		for ($i2 = 0; $i2 < count($sbArr1); $i2++) {
			$b = (int)$sbArr2[$i2];
			$str .= dechex($b & 255);
			$str .= ' ';
		}
		$str .= ']L:';
		$str .= $lengthInBytes;
		return $str;
	}

	public function readBase64(ByteReader $reader, $lengthInBytes) {
		$sbArr = $reader->readSBytes($lengthInBytes);
		return static::byteArrayToBase64($sbArr);
	}

	public function readBoolean(ByteReader $reader, $lengthInBytes) {
		$flag = $lengthInBytes == 1;
		if ($flag) {
			$b = $reader->readByte();
			if ($b != 1)
				throw new \Exception("Boolean error: {$b}");
		}
		return $flag;
	}

	public function readLittleEndianFloat(ByteReader $reader) {
		return $reader->readSingle();
	}

	public function readLittleEndianInt(ByteReader $reader) {
		return $reader->readInt32();
	}

	public function readLittleEndianShort(ByteReader $reader) {
		return $reader->readInt16();
	}

	public function readNumber(ByteReader $reader, $lengthInBytes) {
		$s = "";
		switch ($lengthInBytes) {
			case 1:
				$s .= $reader->readSByte();
				break;

			case 2:
				$s .= $this->readLittleEndianShort($reader);
				break;

			case 4:
				$s .= $this->readLittleEndianInt($reader);
				break;

			default:
				$s .= "0";
				break;
		}
		return $s;
	}

	public function readString(ByteReader $reader, $lengthInBytes) {
		return $reader->readChars($lengthInBytes);
	}

	public function readStringTillZero(ByteReader $reader) {
		$chArr = array();
		$i = 0;
		for ($ch = $reader->readChar(); $ch != chr(0); $ch = $reader->readChar()) {
			$chArr[$i++] = $ch;
		}
		return implode($chArr);
	}

	private static function byteArrayToBase64($a) {
		$i1 = count($a);
		$i2 = $i1 / 3;
		$i3 = $i1 - (3 * $i2);
		$i4 = 4 * (($i1 + 2) / 3);
		$str = "";
		$i5 = 0;
		for ($i6 = 0; $i6 < $i2; $i6++) {
			$i7 = $a[$i5++] & 255;
			$i8 = $a[$i5++] & 255;
			$i9 = $a[$i5++] & 255;
			$str .= static::$intToBase64[$i7 >> 2];
			$str .= static::$intToBase64[(($i7 << 4) & 63) | ($i8 >> 4)];
			$str .= static::$intToBase64[(($i8 << 2) & 63) | ($i9 >> 6)];
			$str .= static::$intToBase64[$i9 & 63];
		}
		if ($i3 != 0) {
			$i10 = $a[$i5++] & 255;
			$str .= static::$intToBase64[$i10 >> 2];
			if ($i3 == 1) {
				$str .= static::$intToBase64[($i10 << 4) & 63];
				$str .= "==";
			} else {
				$i11 = $a[$i5++] & 255;
				$str .= static::$intToBase64[(($i10 << 4) & 63) | ($i11 >> 4)];
				$str .= static::$intToBase64[($i11 << 2) & 63];
				$str .= '=';
			}
		}
		return $str;
	}

	/**
	 * @param \SimpleXMLElement $target
	 * @param \SimpleXMLElement $source
	 */
	private function appendSimpleXml(&$target, &$source) {
		$temp = $target->addChild($source->getName(), (string)$source);
		foreach ($source->attributes() as $attr_key => $attr_value) {
			$temp->addAttribute($attr_key, $attr_value);
		}

		foreach ($source->children() as $child) {
			$this->appendSimpleXml($temp, $child);
		}
	}

}