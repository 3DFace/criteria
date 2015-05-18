<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Greater extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitGreater($this->left, $this->right);
	}

}
