<?php

namespace dface\criteria\node;

abstract class ArithmeticBinary implements Criteria
{

	protected Operand $left;
	protected Operand $right;

	public function __construct(Operand $left, Operand $right)
	{
		$this->left = $left;
		$this->right = $right;
	}

}
