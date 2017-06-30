<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SqlCriteriaBuilderTest extends \PHPUnit_Framework_TestCase {

	/** @var SqlCriteriaBuilder */
	private $builder;
	/** @var Reference */
	private $ref1;
	/** @var StringConstant */
	private $val1;
	/** @var IntegerConstant */
	private $val2;
	/** @var FloatConstant */
	private $val3;
	/** @var Constant */
	private $val4;

	protected function setUp(){
		$this->builder = new SqlCriteriaBuilder();
		$this->ref1 = new Reference('some/ref');
		$this->val1 = new StringConstant('asd');
		$this->val2 = new IntegerConstant(1);
		$this->val3 = new FloatConstant(1.39);
		$this->val4 = new BinaryConstant(hex2bin('FF'));
	}

	function testReference(){
		$this->assertEquals(
			['{i}', ['some/ref']],
			$this->builder->build($this->ref1));
	}

	function testDeepReference(){
		$this->assertEquals(
			['{i}.{i}.{i}', ['a', 'b', 'c']],
			$this->builder->build(new Reference('a.b.c')));
	}

	function testMappedReference(){
		$this->assertEquals(
			['HEX({i}.{i})', ['table','some/ref']],
			$this->builder->build($this->ref1, function($ref){
				return ['HEX({i}.{i})', ['table', $ref]];
			}));
	}

	function testNull(){
		$this->assertEquals(
			['null', []],
			$this->builder->build(new TheNull()));
	}

	function testConstant(){
		$this->assertEquals(
			['{s}', ['asd']],
			$this->builder->build($this->val1));
		$this->assertEquals(
			['{s}', [1]],
			$this->builder->build($this->val2));
		$this->assertEquals(
			['{s}', [1.39]],
			$this->builder->build($this->val3));
		$this->assertEquals(
			['{b}', [hex2bin('ff')]],
			$this->builder->build($this->val4));
		$this->assertEquals(
			['{d}', [1]],
			$this->builder->build(new BoolConstant(true)));
		$this->assertEquals(
			['{d}', [0]],
			$this->builder->build(new BoolConstant(false)));
	}

	protected function assertComparison(Comparison $c, $operator){
		$x = $this->builder->build($c);
		$this->assertEquals(['{i}'.$operator.'{s}', ['some/ref', 1]], $x);
	}

	function testEquals(){
		$this->assertComparison(new Equals($this->ref1, $this->val2), '=');
	}

	function testNotEquals(){
		$this->assertComparison(new NotEquals($this->ref1, $this->val2), '!=');
	}

	function testGreater(){
		$this->assertComparison(new Greater($this->ref1, $this->val2), '>');
	}

	function testGreaterOrEquals(){
		$this->assertComparison(new GreaterOrEquals($this->ref1, $this->val2), '>=');
	}

	function testLess(){
		$this->assertComparison(new Less($this->ref1, $this->val2), '<');
	}

	function testLessOrEquals(){
		$this->assertComparison(new LessOrEquals($this->ref1, $this->val2), '<=');
	}

	function testMatch(){
		$this->assertComparison(new Match($this->ref1, $this->val2), ' LIKE ');
	}

	function testNotMatch(){
		$this->assertComparison(new NotMatch($this->ref1, $this->val2), ' NOT LIKE ');
	}

	function testRegexp(){
		$this->assertComparison(new Regexp($this->ref1, $this->val2), ' RLIKE ');
	}

	function testNotRegexp(){
		$this->assertComparison(new NotRegexp($this->ref1, $this->val2), ' NOT RLIKE ');
	}

	function testIn(){
		$c = new In($this->ref1, [$this->val2, $this->val1]);
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
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val1),
		]);
		$x = $this->builder->build($c);
		$this->assertEquals([
			'({i}={s}) AND ({s}>{s})',
			['some/ref', 1, 1, 'asd']], $x);
	}

	function testOr(){
		$c = new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val1),
		]);
		$x = $this->builder->build($c);
		$this->assertEquals([
			'({i}={s}) OR ({s}>{s})',
			['some/ref', 1, 1, 'asd']], $x);
	}

	function testNot(){
		$c = new LogicalNot(new Equals($this->ref1, $this->val2));
		$x = $this->builder->build($c);
		$this->assertEquals(['NOT ({i}={s})', ['some/ref', 1]], $x);
	}

}

