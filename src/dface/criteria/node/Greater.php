<?php

namespace dface\criteria\node;

class Greater extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitGreater($this->left, $this->right);
	}

}
