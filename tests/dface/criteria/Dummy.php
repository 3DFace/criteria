<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class Dummy {

	/** @var self */
	private $foo;
	/** @var self */
	private $bar;
	/** @var mixed */
	private $val;

	/**
	 * Dummy constructor.
	 * @param Dummy $foo
	 * @param Dummy $bar
	 * @param string $val
	 */
	public function __construct(Dummy $foo = null, Dummy $bar = null, $val = null){
		$this->foo = $foo;
		$this->bar = $bar;
		$this->val = $val;
	}

	function getFoo(){
		return $this->foo;
	}

	function getBar(){
		return $this->bar;
	}

	function getVal(){
		return $this->val;
	}

}
