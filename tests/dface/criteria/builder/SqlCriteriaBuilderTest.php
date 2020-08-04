<?php

namespace dface\criteria\builder;

use dface\criteria\node\BinaryConstant;
use dface\criteria\node\BoolConstant;
use dface\criteria\node\Comparison;
use dface\criteria\node\Constant;
use dface\criteria\node\Equals;
use dface\criteria\node\FloatConstant;
use dface\criteria\node\Greater;
use dface\criteria\node\GreaterOrEquals;
use dface\criteria\node\In;
use dface\criteria\node\IntegerConstant;
use dface\criteria\node\IsNull;
use dface\criteria\node\Less;
use dface\criteria\node\LessOrEquals;
use dface\criteria\node\LogicalAnd;
use dface\criteria\node\LogicalNot;
use dface\criteria\node\LogicalOr;
use dface\criteria\node\Match;
use dface\criteria\node\MatchRegexp;
use dface\criteria\node\NotEquals;
use dface\criteria\node\NotMatch;
use dface\criteria\node\NotMatchRegexp;
use dface\criteria\node\NotNull;
use dface\criteria\node\Reference;
use dface\criteria\node\StringConstant;
use dface\criteria\node\TheNull;
use PHPUnit\Framework\TestCase;

class SqlCriteriaBuilderTest extends TestCase
{

	private SqlCriteriaBuilder $builder;
	private Reference $ref1;
	private StringConstant $val1;
	private IntegerConstant $val2;
	private FloatConstant $val3;
	private Constant $val4;

	protected function setUp() : void
	{
		parent::setUp();
		$this->builder = new SqlCriteriaBuilder();
		$this->ref1 = new Reference('some/ref');
		$this->val1 = new StringConstant('asd');
		$this->val2 = new IntegerConstant(1);
		$this->val3 = new FloatConstant(1.39);
		$this->val4 = new BinaryConstant(hex2bin('FF'));
	}

	function testReference() : void
	{
		self::assertEquals(
			['{i}', ['some/ref']],
			$this->builder->build($this->ref1));
	}

	function testDeepReference() : void
	{
		self::assertEquals(
			['{i}.{i}.{i}', ['a', 'b', 'c']],
			$this->builder->build(new Reference('a.b.c')));
	}

	function testMappedReference() : void
	{
		self::assertEquals(
			['HEX({i}.{i})', ['table', 'some/ref']],
			$this->builder->build($this->ref1, static function ($ref) {
				return ['HEX({i}.{i})', ['table', $ref]];
			}));
	}

	function testNull() : void
	{
		self::assertEquals(
			['null', []],
			$this->builder->build(new TheNull()));
	}

	function testConstant() : void
	{
		self::assertEquals(
			['{s}', ['asd']],
			$this->builder->build($this->val1));
		self::assertEquals(
			['{d}', [1]],
			$this->builder->build($this->val2));
		self::assertEquals(
			['{n}', [1.39]],
			$this->builder->build($this->val3));
		self::assertEquals(
			['{b}', [hex2bin('ff')]],
			$this->builder->build($this->val4));
		self::assertEquals(
			['{d}', [1]],
			$this->builder->build(new BoolConstant(true)));
		self::assertEquals(
			['{d}', [0]],
			$this->builder->build(new BoolConstant(false)));
	}

	protected function assertComparison(Comparison $c, $operator) : void
	{
		$x = $this->builder->build($c);
		self::assertEquals(['{i}'.$operator.'{d}', ['some/ref', 1]], $x);
	}

	function testEquals() : void
	{
		$this->assertComparison(new Equals($this->ref1, $this->val2), '=');
	}

	function testNotEquals() : void
	{
		$this->assertComparison(new NotEquals($this->ref1, $this->val2), '!=');
	}

	function testGreater() : void
	{
		$this->assertComparison(new Greater($this->ref1, $this->val2), '>');
	}

	function testGreaterOrEquals() : void
	{
		$this->assertComparison(new GreaterOrEquals($this->ref1, $this->val2), '>=');
	}

	function testLess() : void
	{
		$this->assertComparison(new Less($this->ref1, $this->val2), '<');
	}

	function testLessOrEquals() : void
	{
		$this->assertComparison(new LessOrEquals($this->ref1, $this->val2), '<=');
	}

	function testMatch() : void
	{
		$this->assertComparison(new Match($this->ref1, $this->val2), ' LIKE ');
	}

	function testNotMatch() : void
	{
		$this->assertComparison(new NotMatch($this->ref1, $this->val2), ' NOT LIKE ');
	}

	function testRegexp() : void
	{
		$this->assertComparison(new MatchRegexp($this->ref1, $this->val2), ' RLIKE ');
	}

	function testNotRegexp() : void
	{
		$this->assertComparison(new NotMatchRegexp($this->ref1, $this->val2), ' NOT RLIKE ');
	}

	function testIn() : void
	{
		$c = new In($this->ref1, [$this->val2, $this->val1]);
		$x = $this->builder->build($c);
		self::assertEquals(['{i} IN ({d}, {s})', ['some/ref', 1, 'asd']], $x);
	}

	function testIsNull() : void
	{
		$c = new IsNull($this->ref1);
		$x = $this->builder->build($c);
		self::assertEquals(['{i} IS NULL', ['some/ref']], $x);
	}

	function testIsNotNull() : void
	{
		$c = new NotNull($this->ref1);
		$x = $this->builder->build($c);
		self::assertEquals(['{i} IS NOT NULL', ['some/ref']], $x);
	}

	function testAnd() : void
	{
		$c = new LogicalAnd([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val1),
		]);
		$x = $this->builder->build($c);
		self::assertEquals([
			'({i}={d}) AND ({d}>{s})',
			['some/ref', 1, 1, 'asd']
		], $x);
	}

	function testOr() : void
	{
		$c = new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val1),
		]);
		$x = $this->builder->build($c);
		self::assertEquals([
			'({i}={d}) OR ({d}>{s})',
			['some/ref', 1, 1, 'asd']
		], $x);
	}

	function testNot() : void
	{
		$c = new LogicalNot(new Equals($this->ref1, $this->val2));
		$x = $this->builder->build($c);
		self::assertEquals(['NOT ({i}={d})', ['some/ref', 1]], $x);
	}

}

