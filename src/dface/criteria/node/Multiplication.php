<?php

namespace dface\criteria\node;

class Multiplication extends ArithmeticBinary
{
	
	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitMultiplication($this->left, $this->right);
	}

}
