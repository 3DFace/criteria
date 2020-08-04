<?php

namespace dface\criteria\node;

class LogicalAnd extends Logical
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitAnd($this->members);
	}

}
