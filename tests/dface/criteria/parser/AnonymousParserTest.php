<?php

namespace dface\criteria\parser;

use dface\criteria\node\Equals;
use dface\criteria\node\In;
use dface\criteria\node\IntegerConstant;
use dface\criteria\node\LogicalAnd;
use dface\criteria\node\LogicalNot;
use dface\criteria\node\LogicalOr;
use dface\criteria\node\Match;
use dface\criteria\node\Node;
use dface\criteria\node\Reference;
use dface\criteria\node\StringConstant;
use PHPUnit\Framework\TestCase;

class AnonymousParserTest extends TestCase
{

	/** @var AnonymousParser */
	private AnonymousParser $parser;
	private Reference $ref;

	protected function setUp() : void
	{
		parent::setUp();
		$lexer = new Lexer();
		$this->parser = new AnonymousParser($lexer, true, true);
		$this->ref = new Reference('x');
	}

	protected function assertExpressionMatchNode($exp, Node $node) : void
	{
		$parsed = $this->parser->parse($this->ref, $exp);
		self::assertEquals($node, $parsed);
	}

	protected function assertExpressionNotMatchNode($exp, Node $node) : void
	{
		$parsed = $this->parser->parse($this->ref, $exp);
		self::assertNotEquals($node, $parsed, "Expression $exp must not match node");
	}

	public function testEmpty() : void
	{
		$c = $this->parser->parse($this->ref, ' ');
		self::assertNull($c);
	}

	public function testUnexpectedReference() : void
	{
		$this->expectException(ParseException::class);
		$this->parser->parse($this->ref, '$y');
	}

	public function testReference() : void
	{
		$this->assertExpressionMatchNode('=$y', new Equals($this->ref, new Reference('y')));
		$this->assertExpressionMatchNode('=$"a name"', new Equals($this->ref, new Reference('a name')));
	}

	public function testIn() : void
	{
		$this->assertExpressionMatchNode('[1,2 , zxc]', new In($this->ref, [
			new IntegerConstant(1),
			new IntegerConstant(2),
			new StringConstant('zxc'),
		]));
	}

	public function testImplicitAnd() : void
	{
		$this->assertExpressionMatchNode('qwe || asd !zxc',
			new LogicalOr([
				new Match($this->ref, new StringConstant('%qwe%')),
				new LogicalAnd([
					new Match($this->ref, new StringConstant('%asd%')),
					new LogicalNot(new Match($this->ref, new StringConstant('%zxc%'))),
				])
			]));
	}

	public function testBrackets() : void
	{
		$this->assertExpressionMatchNode('(=a (=b | (c) | (d | e & f g)))', new LogicalAnd([
			new Equals($this->ref, new StringConstant('a')),
			new LogicalOr([
				new Equals($this->ref, new StringConstant('b')),
				new Match($this->ref, new StringConstant('%c%')),
				new LogicalOr([
					new Match($this->ref, new StringConstant('%d%')),
					new LogicalAnd([
						new Match($this->ref, new StringConstant('%e%')),
						new Match($this->ref, new StringConstant('%f%')),
						new Match($this->ref, new StringConstant('%g%')),
					]),
				])
			])
		]));
	}

}
