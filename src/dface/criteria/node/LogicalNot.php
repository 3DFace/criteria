<?php

namespace dface\criteria\node;

class LogicalNot implements Criteria
{

	private Criteria $criteria;

	public function __construct(Criteria $criteria)
	{
		$this->criteria = $criteria;
	}

	public function acceptNodeVisitor(NodeVisitor $visitor)
	{
		return $visitor->visitNot($this->criteria);
	}

}
