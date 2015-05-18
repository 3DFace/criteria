<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Regexp extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitRegexp($this->left, $this->right);
	}

}
