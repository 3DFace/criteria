<?php

namespace dface\criteria\node;

class NotEquals extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNotEquals($this->left, $this->right);
	}

}
