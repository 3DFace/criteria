<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

use dface\criteria as C;

class ExpressionParser {

	protected $END_CRITERIA_TOKEN;

	/** @var Lexer */
	protected $lexer;
	/** @var Token[] */
	protected $tokens;
	protected $count;
	protected $index;

	function __construct(Lexer $lexer){
		$this->END_CRITERIA_TOKEN = new Token(0, 'END', '');
		$this->lexer = $lexer;
	}

	function parse($pattern){
		$this->tokens = $this->lexer->explode($pattern);
		$this->index = 0;
		$this->count = count($this->tokens);

		$topCriteria = [];
		while($this->getType(0) !== 'END'){
			$topCriteria[] = $this->parseOr();
		}
		switch(count($topCriteria)){
			case 0:
				return null;
			case 1:
				return $topCriteria[0];
			default:
				return new C\LogicalAnd($topCriteria);
		}
	}

	protected function consume(){
		$this->index++;
	}

	protected function sureConsume($type){
		if($this->getType(0) !== $type){
			throw new ParseException($type.' expected at '.$this->index, $this->index);
		}
		$this->consume();
	}

	protected function getType($i){
		$j = $this->index + $i;
		return $j < $this->count ? $this->tokens[$j]->type : 'END';
	}

	protected function getToken($i){
		$j = $this->index + $i;
		return $j < $this->count ? $this->tokens[$j] : $this->END_CRITERIA_TOKEN;
	}

	protected function parseOr(){
		$members = [];
		$members[] = $this->parseAnd();
		while($this->getType(0) === 'OR'){
			$this->consume();
			$members[] = $this->parseAnd();
		}
		return count($members) > 1 ? new C\LogicalOr($members) : $members[0];
	}

	protected function parseAnd(){
		$members = [];
		$members[] = $this->parseCriteria();
		while($this->getType(0) === 'AND'){
			$this->consume();
			$members[] = $this->parseCriteria();
		}
		return count($members) > 1 ? new C\LogicalAnd($members) : $members[0];
	}

	protected function parseCriteria(){
		$type = $this->getType(0);
		switch($type){
			case 'NOT':
				return $this->parseNot();
			case 'LEFT_BRACKET':
				return $this->parseBrackets();
			case 'END':
				throw new ParseException('Unexpected end of input', $this->getToken(0)->location);
			default:
				return $this->parseExpression();
		}
	}

	protected function parseString(){
		$token = $this->getToken(0);
		$this->sureConsume('STRING');
		return new C\StringConstant($token->text);
	}

	protected function parseNumber(){
		$token = $this->getToken(0);
		$this->sureConsume('NUMBER');
		return new C\IntegerConstant(0 + str_replace(',', '.', $token->text));
	}

	protected function parseReference(){
		$token = $this->getToken(0);
		$this->sureConsume('REFERENCE');
		return new C\Reference($token->text);
	}

	protected function parseOperand(){
		$type = $this->getType(0);
		switch($type){
			case 'STRING':
				return $this->parseString();
			case 'NUMBER':
				return $this->parseNumber();
			case 'REFERENCE':
				return $this->parseReference();
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException('Unexpected '.$t->type." '".$t->text."' at ".$loc, $loc);
		}
	}

	protected function parseExpression(){
		$left = $this->parseOperand();
		$type = $this->getType(0);
		switch($type){
			case 'EQUALS':
				return $this->parseEquals($left);
			case 'NOT_EQUALS':
				return $this->parseNotEquals($left);
			case 'MATCH':
				return $this->parseMatch($left);
			case 'NOT_MATCH':
				return $this->parseNotMatch($left);
			case 'REGEXP':
				return $this->parseRegexp($left);
			case 'NOT_REGEXP':
				return $this->parseNotRegexp($left);
			case 'GREATER':
				return $this->parseGreater($left);
			case 'GREATER_OR_EQUALS':
				return $this->parseGreaterOrEquals($left);
			case 'LESS':
				return $this->parseLess($left);
			case 'LESS_OR_EQUALS':
				return $this->parseLessOrEquals($left);
			case 'LEFT_SQUARE_BRACKET':
				return $this->parseIn($left);
			case 'IS_NULL':
				return $this->parseIsNull($left);
			case 'NOT_NULL':
				return $this->parseNotNull($left);
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException('Unexpected '.$t->type." '".$t->text."' at ".$loc, $loc);
		}
	}

	protected function parseNot(){
		$this->sureConsume('NOT');
		return new C\LogicalNot($this->parseCriteria());
	}

	protected function parseEquals($left){
		$this->sureConsume('EQUALS');
		$right = $this->parseOperand();
		return new C\Equals($left, $right);
	}

	protected function parseNotEquals($left){
		$this->sureConsume('NOT_EQUALS');
		$right = $this->parseOperand();
		return new C\NotEquals($left, $right);
	}

	protected function parseGreater($left){
		$this->sureConsume('GREATER');
		$right = $this->parseOperand();
		return new C\Greater($left, $right);
	}

	protected function parseGreaterOrEquals($left){
		$this->sureConsume('GREATER_OR_EQUALS');
		$right = $this->parseOperand();
		return new C\GreaterOrEquals($left, $right);
	}

	protected function parseLess($left){
		$this->sureConsume('LESS');
		$right = $this->parseOperand();
		return new C\Less($left, $right);
	}

	protected function parseLessOrEquals($left){
		$this->sureConsume('LESS_OR_EQUALS');
		$right = $this->parseOperand();
		return new C\LessOrEquals($left, $right);
	}

	protected function parseMatch($left){
		$this->sureConsume('MATCH');
		$right = $this->parseOperand();
		return new C\Match($left, $right);
	}

	protected function parseNotMatch($left){
		$this->sureConsume('NOT_MATCH');
		$right = $this->parseOperand();
		return new C\NotMatch($left, $right);
	}

	protected function parseRegexp($left){
		$this->sureConsume('REGEXP');
		$right = $this->parseOperand();
		return new C\Regexp($left, $right);
	}

	protected function parseNotRegexp($left){
		$this->sureConsume('NOT_REGEXP');
		$right = $this->parseOperand();
		return new C\NotRegexp($left, $right);
	}

	protected function parseIn($left){
		$set = [];
		$this->sureConsume('LEFT_SQUARE_BRACKET');
		if($this->getType(0) !== 'RIGHT_SQUARE_BRACKET'){
			$set[] = $this->parseOperand();
			while($this->getType(0) !== 'RIGHT_SQUARE_BRACKET'){
				$this->sureConsume('COMA');
				$set[] = $this->parseOperand();
			}
		}
		$this->sureConsume('RIGHT_SQUARE_BRACKET');
		return new C\In($left, $set);
	}

	protected function parseIsNull($left){
		$this->sureConsume('IS_NULL');
		return new C\IsNull($left);
	}

	protected function parseNotNull($left){
		$this->sureConsume('NOT_NULL');
		return new C\NotNull($left);
	}

	protected function parseBrackets(){
		$this->sureConsume('LEFT_BRACKET');
		$criteria = $this->parseOr();
		$this->sureConsume('RIGHT_BRACKET');
		return $criteria;
	}

}
