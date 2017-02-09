<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SqlCriteriaBuilderTest extends \PHPUnit_Framework_TestCase {

	/** @var SqlCriteriaBuilder */
	private $builder;
	/** @var Reference */
	private $ref1;
	/** @var Constant */
	private $val1;
	/** @var Constant */
	private $val2;

	protected function setUp(){
		$this->builder = new SqlCriteriaBuilder();
		$this->ref1 = new Reference('some/ref');
		$this->val1 = new Constant(1);
		$this->val2 = new Constant('asd');
	}

	function testReference(){
		$this->assertEquals(
			['{i}', ['some/ref']],
			$this->builder->build($this->ref1));
	}

	function testConstant(){
		$this->assertEquals(
			['{s}', [1]],
			$this->builder->build($this->val1));
	}

	protected function assertComparison(Comparison $c, $operator){
		$x = $this->builder->build($c);
		$this->assertEquals(['{i}'.$operator.'{s}', ['some/ref', 1]], $x);
	}

	function testEquals(){
		$this->assertComparison(new Equals($this->ref1, $this->val1), '=');
	}

	function testNotEquals(){
		$this->assertComparison(new NotEquals($this->ref1, $this->val1), '!=');
	}

	function testGreater(){
		$this->assertComparison(new Greater($this->ref1, $this->val1), '>');
	}

	function testGreaterOrEquals(){
		$this->assertComparison(new GreaterOrEquals($this->ref1, $this->val1), '>=');
	}

	function testLess(){
		$this->assertComparison(new Less($this->ref1, $this->val1), '<');
	}

	function testLessOrEquals(){
		$this->assertComparison(new LessOrEquals($this->ref1, $this->val1), '<=');
	}

	function testMatch(){
		$this->assertComparison(new Match($this->ref1, $this->val1), ' LIKE ');
	}

	function testNotMatch(){
		$this->assertComparison(new NotMatch($this->ref1, $this->val1), ' NOT LIKE ');
	}

	function testRegexp(){
		$this->assertComparison(new Regexp($this->ref1, $this->val1), ' RLIKE ');
	}

	function testNotRegexp(){
		$this->assertComparison(new NotRegexp($this->ref1, $this->val1), ' NOT RLIKE ');
	}

	function testIn(){
		$c = new In($this->ref1, [$this->val1, $this->val2]);
		$x = $this->builder->build($c);
		$this->assertEquals(['{i} IN ({s}, {s})', ['some/ref', 1, 'asd']], $x);
	}

	function testIsNull(){
		$c = new IsNull($this->ref1);
		$x = $this->builder->build($c);
		$this->assertEquals(['{i} IS NULL', ['some/ref']], $x);
	}

	function testIsNotNull(){
		$c = new NotNull($this->ref1);
		$x = $this->builder->build($c);
		$this->assertEquals(['{i} IS NOT NULL', ['some/ref']], $x);
	}

	function testAnd(){
		$c = new LogicalAnd([
			new Equals($this->ref1, $this->val1),
			new Greater($this->val1, $this->val2),
		]);
		$x = $this->builder->build($c);
		$this->assertEquals([
			'({i}={s}) AND ({s}>{s})',
			['some/ref', 1, 1, 'asd']], $x);
	}

	function testOr(){
		$c = new LogicalOr([
			new Equals($this->ref1, $this->val1),
			new Greater($this->val1, $this->val2),
		]);
		$x = $this->builder->build($c);
		$this->assertEquals([
			'({i}={s}) OR ({s}>{s})',
			['some/ref', 1, 1, 'asd']], $x);
	}

	function testNot(){
		$c = new LogicalNot(new Equals($this->ref1, $this->val1));
		$x = $this->builder->build($c);
		$this->assertEquals(['NOT ({i}={s})', ['some/ref', 1]], $x);
	}

}

