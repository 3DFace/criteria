<?php

namespace dface\criteria\node;

class BoolConstant extends Constant
{

	private bool $value;

	public function __construct(bool $value)
	{
		$this->value = $value;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitBoolConstant($this->value);
	}

}
