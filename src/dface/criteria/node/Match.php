<?php

namespace dface\criteria\node;

class Match extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitMatch($this->left, $this->right);
	}

}
