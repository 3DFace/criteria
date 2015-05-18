<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class NotRegexp extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNotRegexp($this->left, $this->right);
	}

}
