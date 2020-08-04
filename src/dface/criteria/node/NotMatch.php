<?php

namespace dface\criteria\node;

class NotMatch extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNotMatch($this->left, $this->right);
	}

}
