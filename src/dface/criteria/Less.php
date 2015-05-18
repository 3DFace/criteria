<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Less extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitLess($this->left, $this->right);
	}

}
