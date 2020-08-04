<?php

namespace dface\criteria\parser;

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{

	function test1() : void
	{
		$lexer = new Lexer();
		$exp = $lexer->explode(<<<'TAG'
='\'\""\\a'
TAG
		);
		self::assertEquals([
			new Token(Token::EQUALS, '=', 0),
			new Token(Token::STRING, '\'""\\a', 1),
			new Token(Token::END, '', 11),
		], $exp);

	}

}
