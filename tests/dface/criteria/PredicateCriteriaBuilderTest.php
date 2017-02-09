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
	/** @var Constant */
	private $val1;
	/** @var Constant */
	private $val2;
	/** @var Constant */
	private $val3;

	protected function setUp(){
		$this->builder = new PredicateCriteriaBuilder();
		$this->ref = new Reference('value');
		$this->ref1 = new Reference('some/ref1');
		$this->ref2 = new Reference('some/ref2');
		$this->val1 = new Constant(1);
		$this->val2 = new Constant('asd');
		$this->val3 = new Constant(10);
		$this->record = [
			'value' => 1,
			'some' => [
				'ref1' => 'asd',
				'ref2' => 2,
			],
		];
	}

	function assertPredicate(Node $node, $result){
		$fn = $this->builder->build($node);
		$this->assertEquals($result, $fn($this->record));
	}

	function testDefaultComparator(){
		$this->assertEquals(-1, PredicateCriteriaBuilder::defaultComparator(10, 20));
		$this->assertEquals(1, PredicateCriteriaBuilder::defaultComparator(20, 10));
		$this->assertEquals(0, PredicateCriteriaBuilder::defaultComparator(20, 20));
		$this->assertEquals(-1, PredicateCriteriaBuilder::defaultComparator('a', 'b'));
		$this->assertEquals(1, PredicateCriteriaBuilder::defaultComparator('b', 'a'));
		$this->assertEquals(0, PredicateCriteriaBuilder::defaultComparator('a', 'a'));
		$this->assertEquals(-1, PredicateCriteriaBuilder::defaultComparator('2', 11));
		$this->assertEquals(1, PredicateCriteriaBuilder::defaultComparator('2a', 11));
		$this->assertEquals(1, PredicateCriteriaBuilder::defaultComparator('11', 3));
		$this->assertEquals(-1, PredicateCriteriaBuilder::defaultComparator('11a', 3));
		$this->assertEquals(0, PredicateCriteriaBuilder::defaultComparator('11', 11));
	}

	function testReference(){
		$this->assertPredicate($this->ref1, 'asd');
	}

	function testConstant(){
		$this->assertPredicate($this->val1, 1);
	}

	function testEquals(){
		$this->assertPredicate(new Equals($this->ref, new Constant(2)), false);
		$this->assertPredicate(new Equals($this->ref, new Constant('1')), true);
	}

	function testNotEquals(){
		$this->assertPredicate(new NotEquals($this->ref, new Constant(2)), true);
		$this->assertPredicate(new NotEquals($this->ref, new Constant(1)), false);
	}

	function testGreater(){
		$this->assertPredicate(new Greater($this->ref, new Constant(0)), true);
		$this->assertPredicate(new Greater($this->ref, new Constant(2)), false);
		$this->assertPredicate(new Greater($this->ref, new Constant(1)), false);
	}

	function testGreaterOrEquals(){
		$this->assertPredicate(new GreaterOrEquals($this->ref, new Constant(0)), true);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new Constant(2)), false);
		$this->assertPredicate(new GreaterOrEquals($this->ref, new Constant(1)), true);
	}

	function testLess(){
		$this->assertPredicate(new Less($this->ref, new Constant(0)), false);
		$this->assertPredicate(new Less($this->ref, new Constant(2)), true);
		$this->assertPredicate(new Less($this->ref, new Constant(1)), false);
	}

	function testLessOrEquals(){
		$this->assertPredicate(new LessOrEquals($this->ref, new Constant(0)), false);
		$this->assertPredicate(new LessOrEquals($this->ref, new Constant(2)), true);
		$this->assertPredicate(new LessOrEquals($this->ref, new Constant(1)), true);
	}

	function testMatch(){
		$this->assertPredicate(new Match($this->ref1, new Constant('asd')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('%asd%')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('%sd%')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('%as%')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('a_d')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('a%d')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('as_')), true);
		$this->assertPredicate(new Match($this->ref1, new Constant('ass')), false);
		$this->assertPredicate(new Match($this->ref1, new Constant('a%z')), false);
		$this->assertPredicate(new Match($this->ref1, new Constant('a_s')), false);
		$this->assertPredicate(new Match($this->ref1, new Constant('zxc')), false);
	}

	function testNotMatch(){
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('asd')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('%asd%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('%sd%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('%as%')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('a_d')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('a%d')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('as_')), false);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('ass')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('a%z')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('a_s')), true);
		$this->assertPredicate(new NotMatch($this->ref1, new Constant('zxc')), true);
	}

	function testRegexp(){
		$this->assertPredicate(new Regexp($this->ref1, new Constant('`asd`')), true);
		$this->assertPredicate(new Regexp($this->ref1, new Constant('`^as.`')), true);
		$this->assertPredicate(new Regexp($this->ref1, new Constant('`^as$`')), false);
	}

	function testNotRegexp(){
		$this->assertPredicate(new NotRegexp($this->ref1, new Constant('`asd`')), false);
		$this->assertPredicate(new NotRegexp($this->ref1, new Constant('`^as.`')), false);
		$this->assertPredicate(new NotRegexp($this->ref1, new Constant('`^as$`')), true);
	}

	function testIn(){
		$this->assertPredicate(new In($this->ref, [new Constant(1), new Constant(2)]), true);
		$this->assertPredicate(new In($this->ref, []), false);
		$this->assertPredicate(new In($this->ref, [new Constant(3), new Constant(2)]), false);
	}

	function testIsNull(){
		$this->assertPredicate(new IsNull($this->ref), false);
		$this->assertPredicate(new IsNull(new Constant(null)), true);
	}

	function testIsNotNull(){
		$this->assertPredicate(new NotNull($this->ref), true);
		$this->assertPredicate(new NotNull(new Constant(null)), false);
	}

	function testAnd(){
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val1),
			new Greater($this->val3, $this->val1),
		]), true);
		$this->assertPredicate(new LogicalAnd([
			new Equals($this->ref, $this->val1),
			new Greater($this->val1, $this->val3),
		]), false);
	}

	function testOr(){
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val1),
			new Greater($this->val3, $this->val1),
		]), true);
		$this->assertPredicate(new LogicalOr([
			new Equals($this->ref1, $this->val1),
			new Greater($this->val1, $this->val3),
		]), false);
	}

	function testNot(){
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val3)), true);
		$this->assertPredicate(new LogicalNot(new Equals($this->ref, $this->val1)), false);
	}

}

