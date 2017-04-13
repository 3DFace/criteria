<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class PredicateCriteriaBuilderTest extends \PHPUnit_Framework_TestCase {

	/** @var PredicateCriteriaBuilder */
	private $builder;
	/** @var array */
	private $record;
	/** @var Reference */
	private $ref;
	/** @var Reference */
	private $ref1;
	/** @var Reference */
	private $ref2;
	/** @var Reference */
	private $ref3;
	/** @var Reference */
	private $ref4;
	/** @var IntegerConstant */
	private $val2;
	/** @var StringConstant */
	private $val1;
	/** @var FloatConstant */
	private $val3;
	/** @var BinaryConstant */
	private $val4;

	protected function setUp(){
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
			],
		];
	}

	function assertPredicate(Node $node, $result){
		$fn = $this->builder->build($node);
		$this->assertEquals($result, $fn($this->record));
	}

	function testReference(){
		$this->assertPredicate($this->ref1, 'asd');
		$this->assertPredicate($this->ref2, 1);
		$this->assertPredicate($this->ref3, 1.39);
		$this->assertPredicate($this->ref4, hex2bin('ff'));
	}

	function testConstant(){
		$this->assertPredicate($this->val1, 'asd');
		$this->assertPredicate($this->val2, 1);
		$this->assertPredicate($this->val3, 1.39);
		$this->assertPredicate($this->val4, hex2bin('ff'));
	}

	function testNull(){
		$this->assertPredicate(new TheNull(), null);
	}

	function testEquals(){
		$this->assertPredicate(new Equals($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new Equals($this->ref, new StringConstant('1')), true);
		$this->assertPredicate(new Equals($this->ref3, new FloatConstant(1.39)), true);
		$this->assertPredicate(new Equals($this->ref4, new BinaryConstant(hex2bin('ff'))), true);
	}

	function testNotEquals(){
		$this->assertPredicate(new NotEquals($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new NotEquals($this->ref, new StringConstant(1)), false);
	}

	function testGreater(){
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(0)), true);
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new Greater($this->ref, new IntegerConstant(1)), false);
	}

	function testGreaterOrEquals(){
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(0)), true);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(2)), false);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new IntegerConstant(1)), true);
	}

	function testLess(){
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(0)), false);
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new Less($this->ref, new IntegerConstant(1)), false);
	}

	function testLessOrEquals(){
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(0)), false);
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(2)), true);
		$this->assertPredicate(new LessOrEquals($this->ref, new IntegerConstant(1)), true);
	}

	function testMatch(){
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

	function testNotMatch(){
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

	function testRegexp(){
		$this->assertPredicate(new Regexp($this->ref1, new StringConstant('`asd`')), true);
		$this->assertPredicate(new Regexp($this->ref1, new StringConstant('`^as.`')), true);
		$this->assertPredicate(new Regexp($this->ref1, new StringConstant('`^as$`')), false);
	}

	function testNotRegexp(){
		$this->assertPredicate(new NotRegexp($this->ref1, new StringConstant('`asd`')), false);
		$this->assertPredicate(new NotRegexp($this->ref1, new StringConstant('`^as.`')), false);
		$this->assertPredicate(new NotRegexp($this->ref1, new StringConstant('`^as$`')), true);
	}

	function testIn(){
		$this->assertPredicate(new In($this->ref, [new IntegerConstant(1), new IntegerConstant(2)]), true);
		$this->assertPredicate(new In($this->ref, []), false);
		$this->assertPredicate(new In($this->ref, [new IntegerConstant(3), new IntegerConstant(2)]), false);
	}

	function testIsNull(){
		$this->assertPredicate(new IsNull($this->ref), false);
		$this->assertPredicate(new IsNull(new TheNull()), true);
	}

	function testIsNotNull(){
		$this->assertPredicate(new NotNull($this->ref), true);
		$this->assertPredicate(new NotNull(new TheNull()), false);
	}

	function testAnd(){
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val2),
			new Greater($this->val3, $this->val2),
		]), true);
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val2),
			new Greater($this->val2, $this->val3),
		]), false);
	}

	function testOr(){
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val3, $this->val2),
		]), true);
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val2),
			new Greater($this->val2, $this->val3),
		]), false);
	}

	function testNot(){
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val3)), true);
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val2)), false);
	}

}

