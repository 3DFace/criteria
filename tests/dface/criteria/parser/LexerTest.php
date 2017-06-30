<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

class LexerTest extends \PHPUnit_Framework_TestCase {

	function test1() {
		$lexer = new Lexer();
		$exp = $lexer->explode(<<<'TAG'
='\'\""\\a'
TAG
		);
		$this->assertEquals([
			new Token(0, 'EQUALS', '='),
			new Token(1, 'STRING', '\'""\\a'),
			new Token(11, 'END', ''),
		], $exp);

	}

}
