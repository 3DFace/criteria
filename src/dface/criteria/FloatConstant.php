<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class FloatConstant extends Constant {

	function __construct($value){
		$this->value = (float) $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitFloatConstant($this->value);
	}

}
