<?php

namespace dface\criteria\node;

class LessOrEquals extends Comparison
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitLessOrEquals($this->left, $this->right);
	}

}
