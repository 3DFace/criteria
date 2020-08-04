<?php

namespace dface\criteria\node;

class NotMatchRegexp extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNotMatchRegexp($this->left, $this->right);
	}

}
