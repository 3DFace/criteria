<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class BoolConstant extends Constant {

	function __construct($value){
		$this->value = (bool) $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitBoolConstant($this->value);
	}

}
