<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class PredicateCriteriaBuilder implements NodeVisitor {

	/** @var callable */
	private $comparator;

	/**
	 * PredicateCriteriaBuilder constructor.
	 * @param callable $customComparator
	 */
	public function __construct($customComparator = null){
		$this->comparator = $customComparator ?: [self::class, 'defaultComparator'];
	}

	function build(Node $criteria){
		return $criteria->acceptNodeVisitor($this);
	}

	function visitConstant($value){
		return function () use ($value){
			return $value;
		};
	}

	function visitReference($name){
		$path = explode('/', $name);
		return function (array $x) use ($path){
			foreach($path as $p){
				if(!isset($x[$p])){
					return null;
				}
				$x = $x[$p];
			}
			return $x;
		};
	}

	static function defaultComparator($v1, $v2){
		if(is_numeric($v1) && is_numeric($v2)){
			if($v1 < $v2){
				return $v1 > $v2 ? 1 : -1;
			}else{
				return $v1 > $v2 ? 1 : 0;
			}
		}else{
			return max(-1, min(1, strcmp($v1, $v2)));
		}
	}

	function visitComparison(Operand $left, Operand $right, array $true_results){
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($true_results, $left_fn, $right_fn){
			$left_val = $left_fn($x);
			$right_val = $right_fn($x);
			$cmp = $this->comparator;
			$result = $cmp($left_val, $right_val);
			return in_array($result, $true_results, true);
		};
	}

	function visitEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [0]);
	}

	function visitNotEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [1, -1]);
	}

	function visitGreater(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [1]);
	}

	function visitGreaterOrEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [1, 0]);
	}

	function visitLess(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [-1]);
	}

	function visitLessOrEquals(Operand $left, Operand $right){
		return $this->visitComparison($left, $right, [-1, 0]);
	}

	function visitMatch(Operand $left, Operand $right){
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($left_fn, $right_fn){
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			$pattern = str_replace(['%', '_'], ['.*', '.'], $pattern);
			$pattern = "`$pattern`i";
			return preg_match($pattern, $subject);
		};
	}

	function visitNotMatch(Operand $left, Operand $right){
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($left_fn, $right_fn){
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			$pattern = str_replace(['%', '_'], ['.*', '.'], $pattern);
			$pattern = "`$pattern`i";
			return !preg_match($pattern, $subject);
		};
	}

	function visitRegexp(Operand $left, Operand $right){
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($left_fn, $right_fn){
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			return preg_match($pattern, $subject);
		};
	}

	function visitNotRegexp(Operand $left, Operand $right){
		$left_fn = $left->acceptNodeVisitor($this);
		$right_fn = $right->acceptNodeVisitor($this);
		return function ($x) use ($left_fn, $right_fn){
			$subject = $left_fn($x);
			$pattern = $right_fn($x);
			return !preg_match($pattern, $subject);
		};
	}

	/**
	 * @param Operand $subj
	 * @param Operand[] $set
	 * @return string
	 */
	function visitIn(Operand $subj, array $set){
		$subj_fn = $subj->acceptNodeVisitor($this);
		$set_fn = [];
		foreach($set as $operand){
			$set_fn[] = $operand->acceptNodeVisitor($this);
		}
		return function ($x) use ($subj_fn, $set_fn){
			$subj_val = $subj_fn($x);
			foreach($set_fn as $fn){
				if($subj_val === $fn($x)){
					return true;
				}
			}
			return false;
		};
	}

	function visitIsNull(Operand $subject){
		$subj_fn = $subject->acceptNodeVisitor($this);
		return function ($x) use ($subj_fn){
			return $subj_fn($x) === null;
		};
	}

	function visitNotNull(Operand $subject){
		$subj_fn = $subject->acceptNodeVisitor($this);
		return function ($x) use ($subj_fn){
			return $subj_fn($x) !== null;
		};
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

	/**
	 * @param Criteria[] $members
	 * @return string
	 */
	function visitAnd(array $members){
		$members_fn = [];
		foreach($members as $criteria){
			$members_fn[] = $criteria->acceptNodeVisitor($this);
		}
		return function ($x) use ($members_fn){
			foreach($members_fn as $fn){
				if(!$fn($x)){
					return false;
				}
			}
			return true;
		};
	}

	function visitOr(array $members){
		$members_fn = [];
		foreach($members as $criteria){
			$members_fn[] = $criteria->acceptNodeVisitor($this);
		}
		return function ($x) use ($members_fn){
			foreach($members_fn as $fn){
				if($fn($x)){
					return true;
				}
			}
			return false;
		};
	}

	function visitNot(Criteria $criteria){
		$criteria_fn = $criteria->acceptNodeVisitor($this);
		return function ($x) use ($criteria_fn){
			return !$criteria_fn($x);
		};
	}

}
