<?php

namespace dface\criteria\node;

class MatchRegexp extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitMatchRegexp($this->left, $this->right);
	}

}
