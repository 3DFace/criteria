<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class IsNull implements Criteria {

	/** @var Operand */
	protected $subject;

	function __construct(Operand $subject){
		$this->subject = $subject;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitIsNull($this->subject);
	}

	function equals(Node $node){
		return $node instanceof IsNull && $this->subject->equals($node->subject);
	}

}
