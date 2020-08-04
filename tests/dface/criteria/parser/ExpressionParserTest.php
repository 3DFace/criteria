<?php

namespace dface\criteria\parser;

use dface\criteria\node\Equals;
use dface\criteria\node\Greater;
use dface\criteria\node\GreaterOrEquals;
use dface\criteria\node\In;
use dface\criteria\node\IntegerConstant;
use dface\criteria\node\IsNull;
use dface\criteria\node\Less;
use dface\criteria\node\LessOrEquals;
use dface\criteria\node\LogicalAnd;
use dface\criteria\node\LogicalOr;
use dface\criteria\node\Match;
use dface\criteria\node\MatchRegexp;
use dface\criteria\node\Node;
use dface\criteria\node\NotEquals;
use dface\criteria\node\NotMatch;
use dface\criteria\node\NotMatchRegexp;
use dface\criteria\node\NotNull;
use dface\criteria\node\Reference;
use PHPUnit\Framework\TestCase;

class ExpressionParserTest extends TestCase
{

	/** @var ExpressionParser */
	private ExpressionParser $parser;
	private Equals $equals;
	private NotEquals $notEquals;
	private Greater $greater;
	private GreaterOrEquals $greaterOrEquals;
	private Less $less;
	private LessOrEquals $lessOrEquals;
	private Match $match;
	private NotMatch $notMatch;
	private MatchRegexp $regexp;
	private NotMatchRegexp $notRegexp;

	protected function setUp() : void
	{
		parent::setUp();
		$lexer = new Lexer();
		$this->parser = new ExpressionParser($lexer, true);
		$ref = new Reference('x');
		$con = new IntegerConstant(1);
		$this->equals = new Equals($ref, $con);
		$this->greater = new Greater($ref, $con);
		$this->greaterOrEquals = new GreaterOrEquals($ref, $con);
		$this->less = new Less($ref, $con);
		$this->lessOrEquals = new LessOrEquals($ref, $con);
		$this->notEquals = new NotEquals($ref, $con);
		$this->match = new Match($ref, $con);
		$this->notMatch = new NotMatch($ref, $con);
		$this->regexp = new MatchRegexp($ref, $con);
		$this->notRegexp = new NotMatchRegexp($ref, $con);
	}

	protected function assertExpressionMatchNode($exp, Node $node) : void
	{
		$parsed = $this->parser->parse($exp);
		self::assertEquals($node, $parsed, "Expression $exp must match node");
	}

	protected function assertExpressionNotMatchNode($exp, Node $node) : void
	{
		$parsed = $this->parser->parse($exp);
		self::assertNotEquals($node, $parsed, "Expression $exp must not match node");
	}

	function testReference() : void
	{
		$this->assertExpressionMatchNode('$x=1', $this->equals);
		$this->assertExpressionNotMatchNode('x=1', $this->equals);
		$this->assertExpressionNotMatchNode("'x'=1", $this->equals);
		$this->assertExpressionNotMatchNode('`y`=1', $this->equals);
	}

	function testConstant() : void
	{
		$this->assertExpressionMatchNode('$x=1', $this->equals);
		$this->assertExpressionNotMatchNode('$x=2', $this->equals);
		$this->assertExpressionNotMatchNode('$x=$y', $this->equals);
	}

	protected function doComparisonTest(Node $x1_node, $operator, $bad_x1_node, $bad_operator) : void
	{
		$this->assertExpressionMatchNode('$x'.$operator.'1', $x1_node);
		$this->assertExpressionNotMatchNode('$x'.$operator.'1', $bad_x1_node);
		$this->assertExpressionNotMatchNode('$x'.$bad_operator.'1', $x1_node);
	}

	function testLogical() : void
	{
		$x = new Equals(new Reference('x'), new IntegerConstant(1));
		$y = new Equals(new Reference('y'), new IntegerConstant(2));
		$z = new Equals(new Reference('z'), new IntegerConstant(3));

		$this->assertExpressionMatchNode('$x=1 & $y=2 & $z=3', new LogicalAnd([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 && $y=2 && $z=3', new LogicalAnd([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 | $y=2 | $z=3', new LogicalOr([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 || $y=2 || $z=3', new LogicalOr([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 | $y=2 & $z=3', new LogicalOr([$x, new LogicalAnd([$y, $z])]));
		$this->assertExpressionMatchNode('($x=1 | $y=2) & $z=3', new LogicalAnd([new LogicalOr([$x, $y]), $z]));
	}

	function testComparison() : void
	{
		$this->doComparisonTest($this->equals, '=', $this->greater, '>');
		$this->doComparisonTest($this->notEquals, '!=', $this->equals, '=');
		$this->doComparisonTest($this->greater, '>', $this->equals, '=');
		$this->doComparisonTest($this->greaterOrEquals, '>=', $this->equals, '=');
		$this->doComparisonTest($this->less, '<', $this->equals, '=');
		$this->doComparisonTest($this->lessOrEquals, '<=', $this->equals, '=');
		$this->doComparisonTest($this->match, '~', $this->greater, '>');
		$this->doComparisonTest($this->notMatch, '!~', $this->greater, '>');
		$this->doComparisonTest($this->regexp, '?', $this->greater, '>');
		$this->doComparisonTest($this->notRegexp, '!?', $this->greater, '>');
	}

	function testIn() : void
	{
		$set = [new IntegerConstant(1), new IntegerConstant(2), new IntegerConstant(3)];
		$bad_set = [new IntegerConstant(1), new IntegerConstant(2)];
		$node = new In(new Reference('x'), $set);
		$bad_node = new In(new Reference('x'), $bad_set);
		$this->assertExpressionMatchNode('$x [1, 2, 3]', $node);
		$this->assertExpressionNotMatchNode('$x [1, 2, 3]', $bad_node);
		$this->assertExpressionNotMatchNode('$x [1, 2, 3]', $this->equals);
		$this->assertExpressionNotMatchNode('$x = 1', $node);
		$this->assertExpressionNotMatchNode('$x [1, 2]', $node);
	}

	function testIsNull() : void
	{
		$node = new IsNull(new Reference('x'));
		$this->assertExpressionMatchNode('$x=!', $node);
		$this->assertExpressionNotMatchNode('$x=1', $node);
		$this->assertExpressionNotMatchNode('$x=!', $this->equals);
	}

	function testNotNull() : void
	{
		$node = new NotNull(new Reference('x'));
		$this->assertExpressionMatchNode('$x!=!', $node);
		$this->assertExpressionNotMatchNode('$x!=1', $node);
		$this->assertExpressionNotMatchNode('$x!=!', $this->equals);
	}

}
