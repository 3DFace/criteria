<?php

namespace dface\criteria\node;

class Subtraction extends ArithmeticBinary
{
	
	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitSubtraction($this->left, $this->right);
	}

}
