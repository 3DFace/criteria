<?php

namespace dface\criteria\node;

class In implements Criteria
{

	private Operand $subject;
	/** @var Operand[] */
	private array $set;

	public function __construct(Operand $subject, array $set)
	{
		$this->subject = $subject;
		$this->set = $set;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitIn($this->subject, $this->set);
	}

}
