<?php

namespace dface\criteria\node;

class Equals extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitEquals($this->left, $this->right);
	}

}
