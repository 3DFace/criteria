<?php

namespace dface\criteria\node;

class IntegerConstant extends Constant
{

	private int $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitIntegerConstant($this->value);
	}

}
