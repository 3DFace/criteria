<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Constant extends Operand {

	protected $value;

	function __construct($value){
		$this->value = $value;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitConstant($this->value);
	}

	function equals(Node $node){
		return $node instanceof Constant && $node->value == $this->value;
	}

}
