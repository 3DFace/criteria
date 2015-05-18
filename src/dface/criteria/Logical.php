<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

abstract class Logical implements Criteria {

	/** @var Criteria[] */
	protected $members;

	function __construct(array $members){
		$this->members = $members;
	}

	function equals(Node $node){
		return $node instanceof static
		&& $this->containsAll($node->members, $this->members)
		&& $this->containsAll($this->members, $node->members);
	}

	/**
	 * @param Criteria[] $haystack
	 * @param Criteria[] $needle
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
	 * @param Criteria[] $set
	 * @param Criteria $c
	 * @return bool
	 */
	protected function isSetContains(array $set, Criteria $c){
		foreach($set as $o){
			if($c->equals($o)){
				return true;
			}
		}
		return false;
	}

}
