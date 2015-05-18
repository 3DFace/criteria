<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

interface NodeVisitor {

	function visitConstant($value);

	function visitReference($name);

	function visitEquals(Operand $left, Operand $right);

	function visitNotEquals(Operand $left, Operand $right);

	function visitGreater(Operand $left, Operand $right);

	function visitGreaterOrEquals(Operand $left, Operand $right);

	function visitLess(Operand $left, Operand $right);

	function visitLessOrEquals(Operand $left, Operand $right);

	function visitMatch(Operand $left, Operand $right);

	function visitNotMatch(Operand $left, Operand $right);

	function visitRegexp(Operand $left, Operand $right);

	function visitNotRegexp(Operand $left, Operand $right);

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

	function visitNot(Criteria $not);

}
