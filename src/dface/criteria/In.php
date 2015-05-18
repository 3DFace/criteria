<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class In implements Criteria {

	/** @var Operand */
	protected $subject;
	/** @var Operand[] */
	protected $set;

	function __construct(Operand $subject, array $set){
		$this->subject = $subject;
		$this->set = $set;
	}

	function acceptNodeVisitor(NodeVisitor $visitor){
		return $visitor->visitIn($this->subject, $this->set);
	}

	function equals(Node $node){
		return $node instanceof In
		&& $this->containsAll($node->set, $this->set)
		&& $this->containsAll($this->set, $node->set);
	}

	/**
	 * @param Operand[] $haystack
	 * @param Operand[] $needle
	 * @return bool
	 */
	protected function containsAll(array $haystack, array $needle){
		foreach($needle as $o){
			if(!$this->isSetContains($haystack, $o)){
				return false;
			}
		}
		return true;
	}

	/**
	 * @param Operand[] $set
	 * @param Operand $op
	 * @return bool
	 */
	protected function isSetContains(array $set, Operand $op){
		foreach($set as $o){
			if($op->equals($o)){
				return true;
			}
		}
		return false;
	}

}
