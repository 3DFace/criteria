<?php

namespace dface\criteria\node;

abstract class ArithmeticBinary extends Operand
{

	protected Operand $left;
	protected Operand $right;

	public function __construct(Operand $left, Operand $right)
	{
		$this->left = $left;
		$this->right = $right;
	}

}
