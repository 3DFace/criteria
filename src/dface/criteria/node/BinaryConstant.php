<?php

namespace dface\criteria\node;

class BinaryConstant extends Constant
{

	private string $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitBinaryConstant($this->value);
	}

}
