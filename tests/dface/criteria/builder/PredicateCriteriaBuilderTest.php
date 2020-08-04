<?php

namespace dface\criteria\builder;

use dface\criteria\node\BinaryConstant;
use dface\criteria\node\BoolConstant;
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
use dface\criteria\node\Node;
use dface\criteria\node\NotEquals;
use dface\criteria\node\NotMatch;
use dface\criteria\node\NotMatchRegexp;
use dface\criteria\node\NotNull;
use dface\criteria\node\Reference;
use dface\criteria\node\StringConstant;
use dface\criteria\node\TheNull;
use PHPUnit\Framework\TestCase;

class PredicateCriteriaBuilderTest extends TestCase
{

	private PredicateCriteriaBuilder $builder;
	private array $record;
	private Reference $ref;
	private Reference $ref1;
	private Reference $ref2;
	private Reference $ref3;
	private Reference $ref4;
	private IntegerConstant $val2;
	private StringConstant $val1;
	private FloatConstant $val3;
	private BinaryConstant $val4;

	protected function setUp() : void
	{
		parent::setUp();
		$this->builder = new PredicateCriteriaBuilder(new ArrayGraphNavigator(), new SimpleComparator());
		$this->ref = new Reference('value');
		$this->ref1 = new Reference('some/ref1');
		$this->ref2 = new Reference('some/ref2');
		$this->ref3 = new Reference('some/ref3');
		$this->ref4 = new Reference('some/ref4');
		$this->val1 = new StringConstant('asd');
		$this->val2 = new IntegerConstant(1);
		$this->val3 = new FloatConstant(1.39);
		$this->val4 = new BinaryConstant(hex2bin('FF'));
		$this->record = [
			'value' => 1,
			'some' => [
				'ref1' => 'asd',
				'ref2' => 1,
				'ref3' => 1.39,
				'ref4' => hex2bin('FF'),
				'ref5' => true,
			],
		];
	}

	public function assertPredicate(Node $node, $result) : void
	{
		$fn = $this->builder->build($node);
		self::assertEquals($result, $fn($this->record));
	}

	public function testReference() : void
	{
		$this->assertPredicate($this->ref1, 'asd');
		$this->assertPredicate($this->ref2, 1);
		$this->assertPredicate($this->ref3, 1.39);
		$this->assertPredicate($this->ref4, hex2bin('ff'));
		$this->assertPredicate(new Reference('some/ref5'), true);
	}

	public function testConstant() : void
	{
		$this->assertPredicate($this->val1, 'asd');
		$this->assertPredicate($this->val2, 1);
		$this->assertPredicate($this->val3, 1.39);
		$this->assertPredicate($this->val4, hex2bin('ff'));
		$this->assertPredicate(new BoolConstant(true), true);
	}

	public function testNull() : void
	{
		$this->assertPredicate(new TheNull(), null);
	}

	public function testEquals() : void
	{
		$this->assertPredicate(new Equals($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new Equals($this->ref, new StringConstant('1')), true);
		$this->assertPredicate(new Equals($this->ref3, new FloatConstant(1.39)), true);
		$this->assertPredicate(new Equals($this->ref4, new BinaryConstant(hex2bin('ff'))), true);
	}

	public function testNotEquals() : void
	{
		$this->assertPredicate(new NotEquals($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new NotEquals($this->ref, new StringConstant(1)), false);
	}

	public function testGreater() : void
	{
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(0)), true);
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(1)), false);
	}

	public function testGreaterOrEquals() : void
	{
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(0)), true);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(1)), true);
	}

	public function testLess() : void
	{
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(0)), false);
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(1)), false);
	}

	public function testLessOrEquals() : void
	{
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(0)), false);
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(1)), true);
	}

	public function testMatch() : void
	{
		$this->assertPredicate(new Match($this->ref1, new StringConstant('asd')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('%asd%')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('%sd%')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('%as%')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('a_d')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('a%d')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('as_')), true);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('ass')), false);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('a%z')), false);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('a_s')), false);
		$this->assertPredicate(new Match($this->ref1, new StringConstant('zxc')), false);
	}

	public function testNotMatch() : void
	{
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('asd')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('%asd%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('%sd%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('%as%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('a_d')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('a%d')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('as_')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('ass')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('a%z')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('a_s')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new StringConstant('zxc')), true);
	}

	public function testRegexp() : void
	{
		$this->assertPredicate(new MatchRegexp($this->ref1, new StringConstant('`asd`')), true);
		$this->assertPredicate(new MatchRegexp($this->ref1, new StringConstant('`^as.`')), true);
		$this->assertPredicate(new MatchRegexp($this->ref1, new StringConstant('`^as$`')), false);
	}

	public function testNotRegexp() : void
	{
		$this->assertPredicate(new NotMatchRegexp($this->ref1, new StringConstant('`asd`')), false);
		$this->assertPredicate(new NotMatchRegexp($this->ref1, new StringConstant('`^as.`')), false);
		$this->assertPredicate(new NotMatchRegexp($this->ref1, new StringConstant('`^as$`')), true);
	}

	public function testIn() : void
	{
		$this->assertPredicate(new In($this->ref, [new IntegerConstant(1), new IntegerConstant(2)]), true);
		$this->assertPredicate(new In($this->ref, []), false);
		$this->assertPredicate(new In($this->ref, [new IntegerConstant(3), new IntegerConstant(2)]), false);
	}

	public function testIsNull() : void
	{
		$this->assertPredicate(new IsNull($this->ref), false);
		$this->assertPredicate(new IsNull(new TheNull()), true);
	}

	public function testIsNotNull() : void
	{
		$this->assertPredicate(new NotNull($this->ref), true);
		$this->assertPredicate(new NotNull(new TheNull()), false);
	}

	public function testAnd() : void
	{
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val2),
			new Greater($this->val3, $this->val2),
		]), true);
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val2),
			new Greater($this->val2, $this->val3),
		]), false);
	}

	public function testOr() : void
	{
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val3, $this->val2),
		]), true);
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val3),
		]), false);
	}

	public function testNot() : void
	{
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val3)), true);
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val2)), false);
	}

}

