<?php

namespace dface\criteria\builder;

use dface\criteria\node\Criteria;
use dface\criteria\node\Node;
use dface\criteria\node\NodeVisitor;
use dface\criteria\node\Operand;

class SqlCriteriaBuilder implements NodeVisitor
{

	/** @var callable */
	private $referenceMapper;

	public function build(Node $criteria, ?callable $referenceMapper = null)
	{
		$this->referenceMapper = $referenceMapper;
		return $criteria->acceptNodeVisitor($this);
	}

	public function visitStringConstant($value) : array
	{
		return ['{s}', [$value]];
	}

	public function visitBinaryConstant($value) : array
	{
		return ['{b}', [$value]];
	}

	public function visitIntegerConstant($value) : array
	{
		return ['{d}', [$value]];
	}

	public function visitFloatConstant($value) : array
	{
		return ['{n}', [$value]];
	}

	public function visitBoolConstant($value) : array
	{
		return ['{d}', [$value ? 1 : 0]];
	}

	public function visitNull() : array
	{
		return ['null', []];
	}

	public function visitReference(string $name) : array
	{
		if ($this->referenceMapper !== null) {
			return ($this->referenceMapper)($name);
		}
		$name_arr = \explode('.', $name);
		if (\count($name_arr) === 1) {
			return ['{i}', $name_arr];
		}
		$sql = \implode('.', \array_fill(0, \count($name_arr), '{i}'));
		return [$sql, $name_arr];
	}

	private function visitComparison(Operand $left, Operand $right, $operator) : array
	{
		[$left_sql, $left_params] = $left->acceptNodeVisitor($this);
		[$right_sql, $right_params] = $right->acceptNodeVisitor($this);
		return [$left_sql.$operator.$right_sql, \array_merge($left_params, $right_params)];
	}

	public function visitEquals(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '=');
	}

	public function visitNotEquals(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '!=');
	}

	public function visitGreater(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '>');
	}

	public function visitGreaterOrEquals(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '>=');
	}

	public function visitLess(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '<');
	}

	public function visitLessOrEquals(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, '<=');
	}

	public function visitMatch(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, ' LIKE ');
	}

	public function visitNotMatch(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, ' NOT LIKE ');
	}

	public function visitMatchRegexp(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, ' RLIKE ');
	}

	public function visitNotMatchRegexp(Operand $left, Operand $right) : array
	{
		return $this->visitComparison($left, $right, ' NOT RLIKE ');
	}

	/**
	 * @param Operand $subj
	 * @param Operand[] $set
	 * @return array
	 */
	public function visitIn(Operand $subj, array $set) : array
	{
		[$subj_sql, $subj_params] = $subj->acceptNodeVisitor($this);
		$set_sql = [];
		$set_params = [];
		foreach ($set as $operand) {
			[$item_sql, $item_params] = $operand->acceptNodeVisitor($this);
			$set_sql[] = $item_sql;
			$set_params[] = $item_params;
		}
		return [
			$subj_sql.' IN ('.\implode(', ', $set_sql).')',
			\array_merge($subj_params, ...$set_params),
		];
	}

	public function visitIsNull(Operand $subj) : array
	{
		[$subj_sql, $subj_params] = $subj->acceptNodeVisitor($this);
		return [$subj_sql.' IS NULL', $subj_params];
	}

	public function visitNotNull(Operand $subj) : array
	{
		[$subj_sql, $subj_params] = $subj->acceptNodeVisitor($this);
		return [$subj_sql.' IS NOT NULL', $subj_params];
	}

	/**
	 * @param Criteria[] $members
	 * @param $operator
	 * @return array
	 */
	private function visitLogical(array $members, $operator) : array
	{
		$members_sql = [];
		$members_params = [];
		foreach ($members as $criteria) {
			[$m_sql, $m_params] = $criteria->acceptNodeVisitor($this);
			$members_sql[] = $m_sql;
			$members_params[] = $m_params;
		}
		return [
			'('.\implode(') '.$operator.' (', $members_sql).')',
			\array_merge(...$members_params),
		];
	}

	public function visitAnd(array $members) : array
	{
		return $this->visitLogical($members, 'AND');
	}

	public function visitOr(array $members) : array
	{
		return $this->visitLogical($members, 'OR');
	}

	public function visitNot(Criteria $subj) : array
	{
		[$subj_sql, $subj_params] = $subj->acceptNodeVisitor($this);
		return ['NOT ('.$subj_sql.')', $subj_params];
	}

}
