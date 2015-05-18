<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class NotNull implements Criteria {

	/** @var Operand */
	protected $subject;

	function __construct(Operand $subject){
		$this->subject = $subject;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitNotNull($this->subject);
	}

	function equals(Node $node){
		return $node instanceof NotNull && $this->subject->equals($node->subject);
	}

}
