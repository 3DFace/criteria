<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

use dface\criteria as C;
use dface\criteria\Node;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase {

	/** @var ExpressionParser */
	protected $parser;

	protected $equals;
	protected $notEquals;
	protected $greater;
	protected $greaterOrEquals;
	protected $less;
	protected $lessOrEquals;
	protected $match;
	protected $notMatch;
	protected $regexp;
	protected $notRegexp;

	public function __construct($name = null, array $data = array(), $dataName = ''){
		parent::__construct($name, $data, $dataName);
		$lexer = new Lexer();
		$this->parser = new ExpressionParser($lexer);
		$ref = new C\Reference("x");
		$con = new C\Constant(1);
		$this->equals = new C\Equals($ref, $con);
		$this->greater = new C\Greater($ref, $con);
		$this->greaterOrEquals = new C\GreaterOrEquals($ref, $con);
		$this->less = new C\Less($ref, $con);
		$this->lessOrEquals = new C\LessOrEquals($ref, $con);
		$this->notEquals = new C\NotEquals($ref, $con);
		$this->match = new C\Match($ref, $con);
		$this->notMatch = new C\NotMatch($ref, $con);
		$this->regexp = new C\Regexp($ref, $con);
		$this->notRegexp = new C\NotRegexp($ref, $con);
	}

	protected function assertExpressionMatchNode($exp, Node $node){
		$parsed = $this->parser->parse($exp);
		$this->assertTrue($node->equals($parsed), "Expression $exp must match node");
	}

	protected function assertExpressionNotMatchNode($exp, Node $node){
		$parsed = $this->parser->parse($exp);
		$this->assertFalse($node->equals($parsed), "Expression $exp must not match node");
	}

	function testReference(){
		$this->assertExpressionMatchNode('$x=1', $this->equals);
		$this->assertExpressionNotMatchNode("x=1", $this->equals);
		$this->assertExpressionNotMatchNode("'x'=1", $this->equals);
		$this->assertExpressionNotMatchNode("`y`=1", $this->equals);
	}

	function testConstant(){
		$this->assertExpressionMatchNode('$x=1', $this->equals);
		$this->assertExpressionNotMatchNode('$x=2', $this->equals);
		$this->assertExpressionNotMatchNode('$x=$y', $this->equals);
	}

	protected function doComparisonTest(Node $x1_node, $operator, $bad_x1_node, $bad_operator){
		$this->assertExpressionMatchNode('$x'.$operator."1", $x1_node);
		$this->assertExpressionNotMatchNode('$x'.$operator."1", $bad_x1_node);
		$this->assertExpressionNotMatchNode('$x'.$bad_operator."1", $x1_node);
	}

	function testLogical(){
		$x = new C\Equals(new C\Reference('x'), new C\Constant(1));
		$y = new C\Equals(new C\Reference('y'), new C\Constant(2));
		$z = new C\Equals(new C\Reference('z'), new C\Constant(3));

		$this->assertExpressionMatchNode('$x=1 & $y=2 & $z=3', new C\LogicalAnd([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 && $y=2 && $z=3', new C\LogicalAnd([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 | $y=2 | $z=3', new C\LogicalOr([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 || $y=2 || $z=3', new C\LogicalOr([$x, $y, $z]));
		$this->assertExpressionMatchNode('$x=1 | $y=2 & $z=3', new C\LogicalOr([$x, new C\LogicalAnd([$y, $z])]));
		$this->assertExpressionMatchNode('($x=1 | $y=2) & $z=3', new C\LogicalAnd([new C\LogicalOr([$x, $y]), $z]));
	}

	function testComparison(){
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

	function testIn(){
		$set = [new C\Constant(1), new C\Constant(2), new C\Constant(3)];
		$bad_set = [new C\Constant(1), new C\Constant(2)];
		$node = new C\In(new C\Reference('x'), $set);
		$bad_node = new C\In(new C\Reference('x'), $bad_set);
		$this->assertExpressionMatchNode('$x [1, 2, 3]', $node);
		$this->assertExpressionNotMatchNode('$x [1, 2, 3]', $bad_node);
		$this->assertExpressionNotMatchNode('$x [1, 2, 3]', $this->equals);
		$this->assertExpressionNotMatchNode('$x = 1', $node);
		$this->assertExpressionNotMatchNode('$x [1, 2]', $node);
	}

	function testIsNull(){
		$node = new C\IsNull(new C\Reference('x'));
		$this->assertExpressionMatchNode('$x=!', $node);
		$this->assertExpressionNotMatchNode('$x=1', $node);
		$this->assertExpressionNotMatchNode('$x=!', $this->equals);
	}

	function testNotNull(){
		$node = new C\NotNull(new C\Reference('x'));
		$this->assertExpressionMatchNode('$x!=!', $node);
		$this->assertExpressionNotMatchNode('$x!=1', $node);
		$this->assertExpressionNotMatchNode('$x!=!', $this->equals);
	}

}
