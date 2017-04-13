<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class TheNull extends Operand {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNull();
	}

	function equals(Node $node){
		return $node instanceof self;
	}

}
