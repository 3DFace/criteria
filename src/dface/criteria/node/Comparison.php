<?php

namespace dface\criteria\node;

abstract class Comparison implements Criteria
{

	protected Operand $left;
	protected Operand $right;

	function __construct(Operand $left, Operand $right)
	{
		$this->left = $left;
		$this->right = $right;
	}

}
