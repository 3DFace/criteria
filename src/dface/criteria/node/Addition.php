<?php

namespace dface\criteria\node;

class Addition extends ArithmeticBinary
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitAddition($this->left, $this->right);
	}

}
