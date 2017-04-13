<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

abstract class Constant extends Operand {

	protected $value;

	function equals(Node $node){
		return $node instanceof static && $node->value === $this->value;
	}

}
