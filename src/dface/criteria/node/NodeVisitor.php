<?php

namespace dface\criteria\node;

interface NodeVisitor
{

	function visitStringConstant(string $value);

	function visitBinaryConstant(string $value);

	function visitIntegerConstant(int $value);

	function visitFloatConstant(float $value);

	function visitBoolConstant(bool $value);

	function visitNull();

	function visitReference(string $name);

	function visitEquals(Operand $left, Operand $right);

	function visitNotEquals(Operand $left, Operand $right);

	function visitGreater(Operand $left, Operand $right);

	function visitGreaterOrEquals(Operand $left, Operand $right);

	function visitLess(Operand $left, Operand $right);

	function visitLessOrEquals(Operand $left, Operand $right);

	function visitMatch(Operand $subject, Operand $pattern);

	function visitNotMatch(Operand $subject, Operand $pattern);

	function visitMatchRegexp(Operand $subject, Operand $pattern);

	function visitNotMatchRegexp(Operand $subject, Operand $pattern);

	/**
	 * @param Operand $subject
	 * @param Operand[] $set
	 * @return mixed
	 */
	function visitIn(Operand $subject, array $set);

	function visitIsNull(Operand $subject);

	function visitNotNull(Operand $subject);

	/**
	 * @param Criteria[] $members
	 * @return mixed
	 */
	function visitAnd(array $members);

	/**
	 * @param Criteria[] $members
	 * @return mixed
	 */
	function visitOr(array $members);

	function visitNot(Criteria $criteria);

	function visitAddition(Operand $left, Operand $right);

	function visitSubtraction(Operand $left, Operand $right);

	function visitMultiplication(Operand $left, Operand $right);

	function visitDivision(Operand $left, Operand $right);

}
