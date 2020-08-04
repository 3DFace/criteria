<?php

namespace dface\criteria\node;

class FloatConstant extends Constant
{

	private float $value;

	public function __construct(float $value)
	{
		$this->value = $value;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitFloatConstant($this->value);
	}

}
