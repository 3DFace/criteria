<?php

namespace dface\criteria\node;

class MatchPattern extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitMatch($this->left, $this->right);
	}

}
