<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class StringConstant extends Constant {

	function __construct($value){
		$this->value = (string) $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitStringConstant($this->value);
	}

}
