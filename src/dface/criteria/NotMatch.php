<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class NotMatch extends Comparison {

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNotMatch($this->left, $this->right);
	}

}
