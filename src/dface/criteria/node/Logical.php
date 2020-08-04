<?php

namespace dface\criteria\node;

abstract class Logical implements Criteria
{

	/** @var Criteria[] */
	protected array $members;

	public function __construct(array $members)
	{
		$this->members = $members;
	}

}
