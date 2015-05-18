<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Equals extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitEquals($this->left, $this->right);
	}

}
