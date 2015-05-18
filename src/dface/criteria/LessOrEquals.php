<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class LessOrEquals extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitLessOrEquals($this->left, $this->right);
	}

}
