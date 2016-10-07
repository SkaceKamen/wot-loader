<?php
namespace Loader\Xml;

class ElementDescriptor
{
    public $dataDescriptor;
    public $nameIndex;

    public function __construct($nameIndex, $dataDescriptor)
    {
        $this->nameIndex = $nameIndex;
        $this->dataDescriptor = $dataDescriptor;
    }

    public function __toString()
    {
        $str = '[';
        $str .= '0x';
        $str .= dechex($this->nameIndex);
        $str .= ':';
        $str .= $this->dataDescriptor;
        return $str;
    }
}