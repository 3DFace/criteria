<?php

namespace dface\criteria\node;

class GreaterOrEquals extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitGreaterOrEquals($this->left, $this->right);
	}

}
