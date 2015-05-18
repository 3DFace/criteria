<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Match extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitMatch($this->left, $this->right);
	}

}
