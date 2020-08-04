<?php

namespace dface\criteria\builder;

class Dummy
{

	private ?Dummy $foo;
	private ?Dummy $bar;
	/** @var mixed */
	private $val;

	public function __construct(Dummy $foo = null, Dummy $bar = null, $val = null)
	{
		$this->foo = $foo;
		$this->bar = $bar;
		$this->val = $val;
	}

	public function getFoo() : ?Dummy
	{
		return $this->foo;
	}

	public function getBar() : ?Dummy
	{
		return $this->bar;
	}

	public function getVal()
	{
		return $this->val;
	}

}
