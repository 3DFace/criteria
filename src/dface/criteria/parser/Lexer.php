<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

class Lexer {

	static private $WORD_BOUNDS = array(
		'?',
		'#',
		'!',
		'$',
		'=',
		'~',
		'>',
		'<',
		'(',
		')',
		'[',
		']',
		'"',
		'\'',
		'&',
		'|',
		',');

	private $EOF = '<EOF>';

	var $expression;
	var $index;

	function explode($expression){
		$this->expression = $expression;
		$this->index = 0;
		$list = array();
		while(true){
			$token = $this->getToken();
			$list[] = $token;
			if($token->type === 'END'){
				break;
			}
		}
		return $list;
	}

	protected function consume(){
		$this->index++;
	}

	protected function get($i){
		$j = $this->index + $i;
		return $j < strlen($this->expression) ? $this->expression[$j] : $this->EOF;
	}

	protected function sureConsume($match){
		if($this->get(0) !== $match){
			throw new ParseException($match.' expected at '.$this->index, $this->index);
		}
		$this->consume();
	}

	protected function escaped($c){
		switch($c){
			case 'n':
				$text = "\n";
				break;
			case 't':
				$text = "\t";
				break;
			case 'r':
				$text = "\r";
				break;
			case '"':
				$text = '"';
				break;
			case '\'':
				$text = '\'';
				break;
			case $this->EOF:
				throw new ParseException('Unexpected end of input', $this->index);
			default:
				$text = '\\'.$c;
		}
		return $text;
	}

	protected function string($quota){
		$location = $this->index;
		$this->sureConsume($quota);
		$text = '';
		while(true){
			$c = $this->get(0);
			if($c === '\\'){
				$this->sureConsume('\\');
				$c = $this->get(0);
				$text .= $this->escaped($c);
				$this->consume();
			} else {
				if($c === $quota){
					$this->consume();
					break;
				}
				if($c === $this->EOF){
					throw new ParseException('Quotation started at '.$location.' is not closed', $this->index);
				}
				$text .= $c;
				$this->consume();
			}
		}
		return new Token($location, 'STRING', $text);
	}

	protected function literal(){
		$location = $this->index;
		$text = '';
		while(true){
			$c = $this->get(0);
			if($c === $this->EOF || ctype_space($c) || in_array($c, self::$WORD_BOUNDS, true)){
				break;
			}
			$text .= $c;
			$this->consume();
		}
		if(is_numeric($text)){
			return new Token($location, 'NUMBER', $text);
		}
		return new Token($location, 'STRING', $text);
	}

	protected function reference(){
		$this->sureConsume('$');
		$location = $this->index;
		$text = '';
		while(true){
			$c = $this->get(0);
			if($c === $this->EOF || ctype_space($c) || in_array($c, self::$WORD_BOUNDS, true)){
				break;
			}
			$text .= $c;
			$this->consume();
		}
		return new Token($location, 'REFERENCE', $text);
	}

	protected function consumeSpace(){
		while(ctype_space($this->get(0))){
			$this->consume();
		}
	}

	protected function getToken(){
		$token = null;
		while(true){
			$this->consumeSpace();
			$c = $this->get(0);
			$location = $this->index;
			switch($c){
				case '!':
					$this->consume();
					$this->consumeSpace();
					switch($this->get(0)){
						case '=':
							$this->consume();
							$this->consumeSpace();
							if($this->get(0) === '!'){
								$this->consume();
								$token = new Token($location, 'NOT_NULL', '!=!');
							}else{
								$token = new Token($location, 'NOT_EQUALS', '!=');
							}
							break 3;
						case '~':
							$this->consume();
							$token = new Token($location, 'NOT_MATCH', '!~');
							break 3;
						case '?':
							$this->consume();
							$token = new Token($location, 'NOT_REGEXP', '!?');
							break 3;
						default:
							$token = new Token($location, 'NOT', '!');
							break 3;
					}
				case '~':
					$this->consume();
					$token = new Token($location, 'MATCH', '~');
					break 2;
				case '?':
					$this->consume();
					$token = new Token($location, 'REGEXP', '?');
					break 2;
				case '=':
					$this->consume();
					$this->consumeSpace();
					if($this->get(0) === '!'){
						$this->consume();
						$token = new Token($location, 'IS_NULL', '=!');
					}else{
						$token = new Token($location, 'EQUALS', '=');
					}
					break 2;
				case '#':
					$this->consume();
					$token = new Token($location, 'NOT_EQUALS', '#');
					break 2;
				case '<':
					$this->consume();
					if($this->get(0) === '='){
						$this->consume();
						$token = new Token($location, 'LESS_OR_EQUALS', '<=');
						break 2;
					}
					$token = new Token($location, 'LESS', '<');
					break 2;
				case '>':
					$this->consume();
					if($this->get(0) === '='){
						$this->consume();
						$token = new Token($location, 'GREATER_OR_EQUALS', '>=');
						break 2;
					}
					$token = new Token($location, 'GREATER', '>');
					break 2;
				case '@':
					$this->consume();
					$token = new Token($location, 'IN', '@');
					break 2;
				case '(':
					$this->consume();
					$token = new Token($location, 'LEFT_BRACKET', '(');
					break 2;
				case ')':
					$this->consume();
					$token = new Token($location, 'RIGHT_BRACKET', ')');
					break 2;
				case '[':
					$this->consume();
					$token = new Token($location, 'LEFT_SQUARE_BRACKET', '[');
					break 2;
				case ']':
					$this->consume();
					$token = new Token($location, 'RIGHT_SQUARE_BRACKET', ']');
					break 2;
				case ',':
					$this->consume();
					$token = new Token($location, 'COMA', ',');
					break 2;
				case '"':
				case '\'':
					$token = $this->string($c);
					break 2;
				case '$':
					$token = $this->reference();
					break 2;
				case '&':
					$this->consume();
					if($this->get(0) === '&'){
						$this->consume();
						$token = new Token($location, 'AND', '&&');
					} else{
						$token = new Token($location, 'AND', '&');
					}
					break 2;
				case '|':
					$this->consume();
					if($this->get(0) === '|'){
						$this->consume();
						$token = new Token($location, 'OR', '||');
					} else{
						$token = new Token($location, 'OR', '|');
					}
					break 2;
				case $this->EOF:
					$token = new Token($location, 'END', '');
					break 2;
				default:
					$token = $this->literal();
					break 2;
			}

		}
		return $token;
	}

}
