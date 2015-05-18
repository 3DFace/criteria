<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class LogicalAnd extends Logical {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitAnd($this->members);
	}

}
