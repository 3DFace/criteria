<?php

namespace dface\criteria\builder;

use dface\criteria\node\Criteria;
use dface\criteria\node\Node;
use dface\criteria\node\NodeVisitor;
use dface\criteria\node\Operand;

class PredicateCriteriaBuilder implements NodeVisitor
{

	private ObjectGraphNavigator $navigator;
	private Comparator $comparator;

	public function __construct(
		ObjectGraphNavigator $navigator,
		Comparator $comparator
	) {
		$this->navigator = $navigator;
		$this->comparator = $comparator;
	}

	public function build(Node $criteria)
	{
		return $criteria->acceptNodeVisitor($this);
	}

	private function visitConstant($value) : callable
	{
		return static function () use ($value) {
			return $value;
		};
	}

	public function visitStringConstant($value) : callable
	{
		return $this->visitConstant($value);
	}

	public function visitBinaryConstant($value) : callable
	{
		return $this->visitConstant($value);
	}

	public function visitIntegerConstant($value) : callable
	{
		return $this->visitConstant($value);
	}

	public function visitFloatConstant($value) : callable
	{
		return $this->visitConstant($value);
	}

	public function visitBoolConstant($value) : callable
	{
		return $this->visitConstant($value);
	}

	public function visitNull() : callable
	{
		return static function () {
			return null;
		};
	}

	public function visitReference($name) : callable
	{
		return function ($x) use ($name) {
			return $this->navigator->getValue($x, $name);
		};
	}

	private function visitComparison(Operand $left, Operand $right, array $true_results) : callable
	{
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($true_results, $left_fn, $right_fn) {
			$left_val = $left_fn($x);
			$right_val = $right_fn($x);
			$result = $this->comparator->compare($left_val, $right_val);
			return \in_array($result, $true_results, true);
		};
	}

	public function visitEquals(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [0]);
	}

	public function visitNotEquals(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [1, -1]);
	}

	public function visitGreater(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [1]);
	}

	public function visitGreaterOrEquals(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [1, 0]);
	}

	public function visitLess(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [-1]);
	}

	public function visitLessOrEquals(Operand $left, Operand $right) : callable
	{
		return $this->visitComparison($left, $right, [-1, 0]);
	}

	public function visitMatch(Operand $subject, Operand $pattern) : callable
	{
		$left_fn = $subject->acceptNodeVisitor($this);
		$right_fn = $pattern->acceptNodeVisitor($this);
		return static function ($x) use ($left_fn, $right_fn) {
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			$pattern = \str_replace(['%', '_'], ['.*', '.'], $pattern);
			$pattern = "`$pattern`i";
			return \preg_match($pattern, $subject);
		};
	}

	public function visitNotMatch(Operand $subject, Operand $pattern) : callable
	{
		$left_fn = $subject->acceptNodeVisitor($this);
		$right_fn = $pattern->acceptNodeVisitor($this);
		return static function ($x) use ($left_fn, $right_fn) {
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			$pattern = \str_replace(['%', '_'], ['.*', '.'], $pattern);
			$pattern = "`$pattern`i";
			return !\preg_match($pattern, $subject);
		};
	}

	public function visitMatchRegexp(Operand $subject, Operand $pattern) : callable
	{
		$left_fn = $subject->acceptNodeVisitor($this);
		$right_fn = $pattern->acceptNodeVisitor($this);
		return static function ($x) use ($left_fn, $right_fn) {
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			return \preg_match($pattern, $subject);
		};
	}

	public function visitNotMatchRegexp(Operand $subject, Operand $pattern) : callable
	{
		$left_fn = $subject->acceptNodeVisitor($this);
		$right_fn = $pattern->acceptNodeVisitor($this);
		return static function ($x) use ($left_fn, $right_fn) {
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			return !\preg_match($pattern, $subject);
		};
	}

	/**
	 * @param Operand $subject
	 * @param Operand[] $set
	 * @return callable
	 */
	public function visitIn(Operand $subject, array $set) : callable
	{
		$subj_fn = $subject->acceptNodeVisitor($this);
		$set_fn = [];
		foreach ($set as $operand) {
			$set_fn[] = $operand->acceptNodeVisitor($this);
		}
		return static function ($x) use ($subj_fn, $set_fn) {
			$subj_val = $subj_fn($x);
			foreach ($set_fn as $fn) {
				if ($subj_val === $fn($x)) {
					return true;
				}
			}
			return false;
		};
	}

	public function visitIsNull(Operand $subject) : callable
	{
		$subj_fn = $subject->acceptNodeVisitor($this);
		return static function ($x) use ($subj_fn) {
			return $subj_fn($x) === null;
		};
	}

	public function visitNotNull(Operand $subject) : callable
	{
		$subj_fn = $subject->acceptNodeVisitor($this);
		return static function ($x) use ($subj_fn) {
			return $subj_fn($x) !== null;
		};
	}

	/**
	 * @param Criteria[] $members
	 * @return callable
	 */
	public function visitAnd(array $members) : callable
	{
		$members_fn = [];
		foreach ($members as $criteria) {
			$members_fn[] = $criteria->acceptNodeVisitor($this);
		}
		return static function ($x) use ($members_fn) {
			foreach ($members_fn as $fn) {
				if (!$fn($x)) {
					return false;
				}
			}
			return true;
		};
	}

	public function visitOr(array $members) : callable
	{
		$members_fn = [];
		foreach ($members as $criteria) {
			$members_fn[] = $criteria->acceptNodeVisitor($this);
		}
		return static function ($x) use ($members_fn) {
			foreach ($members_fn as $fn) {
				if ($fn($x)) {
					return true;
				}
			}
			return false;
		};
	}

	public function visitNot(Criteria $criteria) : callable
	{
		$criteria_fn = $criteria->acceptNodeVisitor($this);
		return static function ($x) use ($criteria_fn) {
			return !$criteria_fn($x);
		};
	}

	private function visitArithmeticBinary(Operand $left, Operand $right, callable $operator) : callable
	{
		$left_accessor = $left->acceptNodeVisitor($this);
		$right_accessor = $right->acceptNodeVisitor($this);
		return static function ($x) use ($left_accessor, $right_accessor, $operator) {
			$left_val = $left_accessor($x);
			$right_val = $right_accessor($x);
			return $operator($left_val, $right_val);
		};
	}

	function visitAddition(Operand $left, Operand $right) : callable
	{
		return $this->visitArithmeticBinary($left, $right, function($v1, $v2){
			return $v1 + $v2;
		});
	}

	function visitSubtraction(Operand $left, Operand $right) : callable
	{
		return $this->visitArithmeticBinary($left, $right, function($v1, $v2){
			return $v1 - $v2;
		});
	}

	function visitMultiplication(Operand $left, Operand $right) : callable
	{
		return $this->visitArithmeticBinary($left, $right, function($v1, $v2){
			return $v1 * $v2;
		});
	}

	function visitDivision(Operand $left, Operand $right) : callable
	{
		return $this->visitArithmeticBinary($left, $right, function($v1, $v2){
			return $v1 / $v2;
		});
	}

}
