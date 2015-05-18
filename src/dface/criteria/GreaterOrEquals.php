<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class GreaterOrEquals extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitGreaterOrEquals($this->left, $this->right);
	}

}
