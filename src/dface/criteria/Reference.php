<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Reference extends Operand {

	protected $reference;

	function __construct($reference){
		$this->reference = $reference;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitReference($this->reference);
	}

	function equals(Node $node){
		return $node instanceof Reference && $node->reference === $this->reference;
	}

}
