<?php

namespace dface\criteria\node;

class Division extends ArithmeticBinary
{
	
	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitDivision($this->left, $this->right);
	}

}
