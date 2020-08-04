<?php

namespace dface\criteria\node;

class LogicalOr extends Logical
{

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitOr($this->members);
	}

}
