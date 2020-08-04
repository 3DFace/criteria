<?php

namespace dface\criteria\node;

class Less extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitLess($this->left, $this->right);
	}

}
