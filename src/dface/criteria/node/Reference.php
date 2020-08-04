<?php

namespace dface\criteria\node;

class Reference extends Operand
{

	private string $reference;

	public function __construct(string $reference)
	{
		$this->reference = $reference;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitReference($this->reference);
	}

}
