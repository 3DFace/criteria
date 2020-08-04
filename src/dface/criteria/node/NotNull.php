<?php

namespace dface\criteria\node;

class NotNull implements Criteria
{

	private Operand $subject;

	public function __construct(Operand $subject)
	{
		$this->subject = $subject;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNotNull($this->subject);
	}

}
