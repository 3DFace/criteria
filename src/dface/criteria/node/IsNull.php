<?php

namespace dface\criteria\node;

class IsNull implements Criteria
{

	private Operand $subject;

	public function __construct(Operand $subject)
	{
		$this->subject = $subject;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitIsNull($this->subject);
	}

}
