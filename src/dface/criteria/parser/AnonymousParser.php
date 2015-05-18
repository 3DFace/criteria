<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

use dface\criteria as C;

class AnonymousParser {

	protected $END_CRITERIA_TOKEN;

	/** @var Lexer */
	protected $lexer;
	/** @var C\Operand */
	protected $operand;
	/** @var Token[] */
	protected $tokens;
	protected $count;
	protected $index;

	function __construct(Lexer $lexer){
		$this->END_CRITERIA_TOKEN = new Token(0, 'END', "");
		$this->lexer = $lexer;
	}

	function parse(C\Operand $operand, $pattern){
		$this->operand = $operand;
		$this->tokens = $this->lexer->explode($pattern);
		$this->index = 0;
		$this->count = count($this->tokens);

		$topCriteria = [];
		while($this->getType(0) != 'END'){
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
		if($this->getType(0) != $type){
			throw new ParseException($type." expected at ".$this->index, $this->index);
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
		while(true){
			switch($type = $this->getType(0)){
				case 'AND':
					$this->consume();
					$members[] = $this->parseCriteria();
					break;
				// т.к пробел работает как AND
				// todo: исправить Lexer? добавить WHITE_SPACE?
				case 'STRING':
				case 'NUMBER':
					$token = $this->getToken(0);
					$this->sureConsume($type);
					$members[] = new C\Match($this->operand, new C\Constant("%".$token->text."%"));
					break;
				default:
					break 2;
			}
		}
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
				throw new ParseException("Unexpected end of input", $this->getToken(0)->location);
			case 'EQUALS':
				return $this->parseEquals();
			case 'NOT_EQUALS':
				return $this->parseNotEquals();
			case 'MATCH':
				return $this->parseMatch();
			case 'NOT_MATCH':
				return $this->parseNotMatch();
			case 'REGEXP':
				return $this->parseRegexp();
			case 'NOT_REGEXP':
				return $this->parseNotRegexp();
			case 'GREATER':
				return $this->parseGreater();
			case 'GREATER_OR_EQUALS':
				return $this->parseGreaterOrEquals();
			case 'LESS':
				return $this->parseLess();
			case 'LESS_OR_EQUALS':
				return $this->parseLessOrEquals();
			case 'LEFT_SQUARE_BRACKET':
				return $this->parseIn();
			case 'IS_NULL':
				return $this->parseIsNull();
			case 'NOT_NULL':
				return $this->parseNotNull();
			case 'STRING':
			case 'NUMBER':
				$token = $this->getToken(0);
				$this->sureConsume($type);
				return new C\Match($this->operand, new C\Constant("%".$token->text."%"));
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException("Unexpected ".$t->type." '".$t->text."' at ".$loc, $loc);
		}
	}

	protected function parseNot(){
		$this->sureConsume('NOT');
		if($this->getType(0) === 'END'){
			return new C\LogicalAnd(array(
				new C\NotNull($this->operand),
				new C\NotEquals($this->operand, new C\Constant('')),
			));
		}else{
			return new C\LogicalNot($this->parseCriteria());
		}

	}

	protected function parseEquals(){
		$this->sureConsume('EQUALS');
		if($this->getType(0) === 'END'){
			return new C\LogicalOr(array(
				new C\IsNull($this->operand),
				new C\Equals($this->operand, new C\Constant('')),
			));
		}else{
			$c = $this->parseOperand();
			return new C\Equals($this->operand, $c);
		}
	}

	protected function parseNotEquals(){
		$this->sureConsume('NOT_EQUALS');
		if($this->getType(0) === 'END'){
			return new C\LogicalAnd(array(
				new C\NotNull($this->operand),
				new C\NotEquals($this->operand, new C\Constant('')),
			));
		}else{
			$c = $this->parseOperand();
			return new C\NotEquals($this->operand, $c);
		}
	}

	protected function parseGreater(){
		$this->sureConsume('GREATER');
		if($this->getType(0) === 'END'){
			return new C\LogicalAnd(array(
				new C\NotNull($this->operand),
				new C\Greater($this->operand, new C\Constant('')),
			));
		}else{
			$c = $this->parseOperand();
			return new C\Greater($this->operand, $c);
		}
	}

	protected function parseGreaterOrEquals(){
		$this->sureConsume('GREATER_OR_EQUALS');
		$c = $this->parseOperand();
		return new C\GreaterOrEquals($this->operand, $c);
	}

	protected function parseLess(){
		$this->sureConsume('LESS');
		$c = $this->parseOperand();
		return new C\Less($this->operand, $c);
	}

	protected function parseLessOrEquals(){
		$this->sureConsume('LESS_OR_EQUALS');
		$c = $this->parseOperand();
		return new C\LessOrEquals($this->operand, $c);
	}

	protected function parseMatch(){
		$this->sureConsume('MATCH');
		$c = $this->parseOperand();
		return new C\Match($this->operand, $c);
	}

	protected function parseNotMatch(){
		$this->sureConsume('NOT_MATCH');
		$c = $this->parseOperand();
		return new C\NotMatch($this->operand, $c);
	}

	protected function parseRegexp(){
		$this->sureConsume('REGEXP');
		$c = $this->parseOperand();
		return new C\Regexp($this->operand, $c);
	}

	protected function parseNotRegexp(){
		$this->sureConsume('NOT_REGEXP');
		$c = $this->parseOperand();
		return new C\NotRegexp($this->operand, $c);
	}

	protected function parseIn(){
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
		return new C\In($this->operand, $set);
	}

	protected function parseIsNull(){
		$this->sureConsume('IS_NULL');
		return new C\IsNull($this->operand);
	}

	protected function parseNotNull(){
		$this->sureConsume('NOT_NULL');
		return new C\NotNull($this->operand);
	}

	protected function parseBrackets(){
		$this->sureConsume('LEFT_BRACKET');
		$criteria = $this->parseOr();
		$this->sureConsume('RIGHT_BRACKET');
		return $criteria;
	}

	protected function parseString(){
		$token = $this->getToken(0);
		$this->sureConsume('STRING');
		return new C\Constant($token->text);
	}

	protected function parseNumber(){
		$token = $this->getToken(0);
		$this->sureConsume('NUMBER');
		return new C\Constant(str_replace(',', '.', $token->text));
	}

	protected function parseReference(){
		$token = $this->getToken(0);
		$this->sureConsume('REFERENCE');
		return new C\Reference($token->text);
	}

	protected function parseOperand() {
		$type = $this->getType(0);
		switch($type){
			case 'STRING':
				return $this->parseString();
			case 'NUMBER':
				return $this->parseNumber();
//			case 'REFERENCE':
//				return $this->parseReference();
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException("Unexpected ".$t->type." '".$t->text."' at ".$loc, $loc);
		}
	}

}
