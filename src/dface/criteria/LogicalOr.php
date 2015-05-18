<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class LogicalOr extends Logical {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitOr($this->members);
	}

}
