<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class BinaryConstant extends Constant {

	function __construct($value){
		$this->value = (string) $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitBinaryConstant($this->value);
	}

}
