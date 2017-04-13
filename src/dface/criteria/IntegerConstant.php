<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class IntegerConstant extends Constant {

	function __construct($value){
		$this->value = (int) $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitIntegerConstant($this->value);
	}

}
