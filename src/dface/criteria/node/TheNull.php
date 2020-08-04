<?php

namespace dface\criteria\node;

class TheNull extends Operand
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNull();
	}

}
