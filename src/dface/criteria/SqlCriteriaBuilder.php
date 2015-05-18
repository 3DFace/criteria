<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SqlCriteriaBuilder implements NodeVisitor {

	protected $parameters;
	protected $patternBuilder;
	protected $referenceMapper;

	function __construct(){
		$this->parameters = [];
	}

	function build(Criteria $criteria, $referenceMapper = null){
		$this->parameters = [];
		$this->referenceMapper = $referenceMapper;
		$sql = $criteria->acceptNodeVisitor($this);
		return array($sql, $this->parameters);
	}

	function visitConstant($value){
		$this->parameters[] = $value;
		return "{s}";
	}

	function visitReference($name){
		$mapper = $this->referenceMapper;
		return $mapper ? $mapper($name) : $name;
	}

	function visitComparison(Operand $left, Operand $right, $operator){
		return
			$left->acceptNodeVisitor($this).
			$operator.
			$right->acceptNodeVisitor($this);
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
	 * @return string
	 */
	function visitIn(Operand $subj, array $set){
		$subj = $subj->acceptNodeVisitor($this);
		$result = [];
		foreach($set as $operand){
			$result[] = $operand->acceptNodeVisitor($this);
		}
		return $subj.' IN ('.implode(', ', $result).')';
	}

	function visitIsNull(Operand $subject){
		return $subject->acceptNodeVisitor($this).' IS NULL';
	}

	function visitNotNull(Operand $subject){
		return $subject->acceptNodeVisitor($this).' IS NOT NULL';
	}

	/**
	 * @param Criteria[] $members
	 * @param $operator
	 * @return string
	 */
	function visitLogical(array $members, $operator){
		$result = [];
		foreach($members as $criteria){
			$result[] = $criteria->acceptNodeVisitor($this);
		}
		return '('.implode(') '.$operator.' (', $result).')';
	}

	function visitAnd(array $members){
		return $this->visitLogical($members, 'AND');
	}

	function visitOr(array $members){
		return $this->visitLogical($members, 'OR');
	}

	function visitNot(Criteria $criteria){
		return 'NOT ('.$criteria->acceptNodeVisitor($this).')';
	}

}
