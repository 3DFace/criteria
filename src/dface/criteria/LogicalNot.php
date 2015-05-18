<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class LogicalNot implements Criteria {

	/** @var Criteria */
	protected $criteria;

	function __construct(Criteria $criteria){
		$this->criteria = $criteria;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNot($this->criteria);
	}

	function equals(Node $node){
		return $node instanceof LogicalNot && $this->criteria->equals($node->criteria);
	}

}
