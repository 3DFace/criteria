<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SqlCriteriaBuilder implements NodeVisitor {

	private $referenceMapper;

	function build(Node $criteria, $referenceMapper = null){
		$this->referenceMapper = $referenceMapper;
		return $criteria->acceptNodeVisitor($this);
	}

	function visitStringConstant($value){
		return ['{s}', [$value]];
	}

	function visitBinaryConstant($value){
		return ['{b}', [$value]];
	}

	function visitIntegerConstant($value){
		return ['{d}', [$value]];
	}

	function visitFloatConstant($value){
		return ['{n}', [$value]];
	}

	function visitBoolConstant($value){
		return ['{d}', [$value ? 1 : 0]];
	}

	function visitNull(){
		return ['null', []];
	}

	function visitReference($name){
		if($this->referenceMapper !== null){
			$mapper = $this->referenceMapper;
			return $mapper($name);
		}
		return ['{i}',  [$name]];
	}

	function visitComparison(Operand $left, Operand $right, $operator){
		list($left_sql, $left_params) =  $left->acceptNodeVisitor($this);
		list($right_sql, $right_params) =  $right->acceptNodeVisitor($this);
		return [$left_sql.$operator.$right_sql, array_merge($left_params, $right_params)];
	}

	function visitEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '=');
	}

	function visitNotEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '!=');
	}

	function visitGreater(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '>');
	}

	function visitGreaterOrEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '>=');
	}

	function visitLess(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '<');
	}

	function visitLessOrEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, '<=');
	}

	function visitMatch(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, ' LIKE ');
	}

	function visitNotMatch(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, ' NOT LIKE ');
	}

	function visitRegexp(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, ' RLIKE ');
	}

	function visitNotRegexp(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, ' NOT RLIKE ');
	}

	/**
	 * @param Operand $subj
	 * @param Operand[] $set
	 * @return array
	 */
	function visitIn(Operand $subj, array $set){
		list($subj_sql, $subj_params) = $subj->acceptNodeVisitor($this);
		$set_sql = [];
		$set_params = [];
		foreach($set as $operand){
			list($item_sql, $item_params) = $operand->acceptNodeVisitor($this);
			$set_sql[] = $item_sql;
			$set_params[] = $item_params;
		}
		return [
			$subj_sql.' IN ('.implode(', ', $set_sql).')',
			array_merge($subj_params, ...$set_params),
		];
	}

	function visitIsNull(Operand $subj){
		list($subj_sql, $subj_params) = $subj->acceptNodeVisitor($this);
		return[$subj_sql.' IS NULL', $subj_params];
	}

	function visitNotNull(Operand $subj){
		list($subj_sql, $subj_params) = $subj->acceptNodeVisitor($this);
		return[$subj_sql.' IS NOT NULL', $subj_params];
	}

	/**
	 * @param Criteria[] $members
	 * @param $operator
	 * @return array
	 */
	function visitLogical(array $members, $operator){
		$members_sql = [];
		$members_params = [];
		foreach($members as $criteria){
			list($m_sql, $m_params) = $criteria->acceptNodeVisitor($this);
			$members_sql[] = $m_sql;
			$members_params[] = $m_params;
		}
		return [
			'('.implode(') '.$operator.' (', $members_sql).')',
			array_merge(...$members_params),
		];
	}

	function visitAnd(array $members){
		return $this->visitLogical($members, 'AND');
	}

	function visitOr(array $members){
		return $this->visitLogical($members, 'OR');
	}

	function visitNot(Criteria $subj){
		list($subj_sql, $subj_params) = $subj->acceptNodeVisitor($this);
		return ['NOT ('.$subj_sql.')', $subj_params];
	}

}
