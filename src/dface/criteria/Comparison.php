<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

abstract class Comparison implements Criteria {

	/** @var Operand */
	protected $left;
	/** @var Operand */
	protected $right;

	function __construct(Operand $left, Operand $right){
		$this->left = $left;
		$this->right = $right;
	}

	protected function operandsEquals(Comparison $node){
		return $node->left->equals($this->left) && $node->right->equals($this->right);
	}

	function equals(Node $node){
		return $node instanceof static && $this->operandsEquals($node);
	}

}
