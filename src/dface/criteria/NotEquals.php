<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class NotEquals extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNotEquals($this->left, $this->right);
	}

}
