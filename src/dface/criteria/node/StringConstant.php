<?php

namespace dface\criteria\node;

class StringConstant extends Constant
{

	private string $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitStringConstant($this->value);
	}

}
