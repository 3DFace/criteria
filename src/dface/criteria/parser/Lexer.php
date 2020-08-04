<?php

namespace dface\criteria\parser;

class Lexer
{

	static private array $WORD_BOUNDS = [
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
		','
	];

	private string $EOF = '<EOF>';

	private string $expression;
	private int $index;

	/**
	 * @param $expression
	 * @return array
	 * @throws ParseException
	 */
	public function explode($expression) : array
	{
		$this->expression = $expression;
		$this->index = 0;
		$list = [];
		while (true) {
			$token = $this->getToken();
			$list[] = $token;
			if ($token->type === Token::END) {
				break;
			}
		}
		return $list;
	}

	private function consume() : void
	{
		$this->index++;
	}

	private function get(int $i) : string
	{
		$j = $this->index + $i;
		return $j < \strlen($this->expression) ? $this->expression[$j] : $this->EOF;
	}

	/**
	 * @param string $match
	 * @throws ParseException
	 */
	private function sureConsume(string $match) : void
	{
		if ($this->get(0) !== $match) {
			throw new ParseException($match.' expected at '.$this->index, $this->index);
		}
		$this->consume();
	}

	/**
	 * @param string $c
	 * @return string
	 * @throws ParseException
	 */
	private function escaped(string $c) : string
	{
		switch ($c) {
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
			case '\\':
				$text = '\\';
				break;
			case $this->EOF:
				throw new ParseException('Unexpected end of input', $this->index);
			default:
				$text = '\\'.$c;
		}
		return $text;
	}

	/**
	 * @param string $quota
	 * @return string
	 * @throws ParseException
	 */
	private function quotedString(string $quota) : string
	{
		$location = $this->index;
		$this->sureConsume($quota);
		$text = '';
		while (true) {
			$c = $this->get(0);
			if ($c === '\\') {
				$this->sureConsume('\\');
				$c = $this->get(0);
				$text .= $this->escaped($c);
				$this->consume();
			}else {
				if ($c === $quota) {
					$this->consume();
					break;
				}
				if ($c === $this->EOF) {
					throw new ParseException('Quotation started at '.$location.' is not closed', $this->index);
				}
				$text .= $c;
				$this->consume();
			}
		}
		return $text;
	}

	private function literal() : string
	{
		$text = '';
		while (true) {
			$c = $this->get(0);
			if ($c === $this->EOF || \ctype_space($c) || \in_array($c, self::$WORD_BOUNDS, true)) {
				break;
			}
			$text .= $c;
			$this->consume();
		}
		return $text;
	}

	private function consumeSpace() : void
	{
		while (\ctype_space($this->get(0))) {
			$this->consume();
		}
	}

	/**
	 * @return Token
	 * @throws ParseException
	 */
	private function getToken() : Token
	{
		$token = null;
		while (true) {
			$this->consumeSpace();
			$c = $this->get(0);
			$location = $this->index;
			switch ($c) {
				case '!':
					$this->consume();
					$this->consumeSpace();
					switch ($this->get(0)) {
						case '=':
							$this->consume();
							$this->consumeSpace();
							if ($this->get(0) === '!') {
								$this->consume();
								$token = new Token(Token::NOT_NULL, '!=!', $location);
							}else {
								$token = new Token(Token::NOT_EQUALS, '!=', $location);
							}
							break 3;
						case '~':
							$this->consume();
							$token = new Token(Token::NOT_MATCH, '!~', $location);
							break 3;
						case '?':
							$this->consume();
							$token = new Token(Token::NOT_MATCH_REGEXP, '!?', $location);
							break 3;
						default:
							$token = new Token(Token::NOT, '!', $location);
							break 3;
					}
				case '~':
					$this->consume();
					$token = new Token(Token::MATCH, '~', $location);
					break 2;
				case '?':
					$this->consume();
					$token = new Token(Token::MATCH_REGEXP, '?', $location);
					break 2;
				case '=':
					$this->consume();
					$this->consumeSpace();
					if ($this->get(0) === '!') {
						$this->consume();
						$token = new Token(Token::IS_NULL, '=!', $location);
					}else {
						$token = new Token(Token::EQUALS, '=', $location);
					}
					break 2;
				case '#':
					$this->consume();
					$token = new Token(Token::NOT_EQUALS, '#', $location);
					break 2;
				case '<':
					$this->consume();
					if ($this->get(0) === '=') {
						$this->consume();
						$token = new Token(Token::LESS_OR_EQUALS, '<=', $location);
						break 2;
					}
					$token = new Token(Token::LESS, '<', $location);
					break 2;
				case '>':
					$this->consume();
					if ($this->get(0) === '=') {
						$this->consume();
						$token = new Token(Token::GREATER_OR_EQUALS, '>=', $location);
						break 2;
					}
					$token = new Token(Token::GREATER, '>', $location);
					break 2;
				case '(':
					$this->consume();
					$token = new Token(Token::LEFT_BRACKET, '(', $location);
					break 2;
				case ')':
					$this->consume();
					$token = new Token(Token::RIGHT_BRACKET, ')', $location);
					break 2;
				case '[':
					$this->consume();
					$token = new Token(Token::LEFT_SQUARE_BRACKET, '[', $location);
					break 2;
				case ']':
					$this->consume();
					$token = new Token(Token::RIGHT_SQUARE_BRACKET, ']', $location);
					break 2;
				case ',':
					$this->consume();
					$token = new Token(Token::COMA, ',', $location);
					break 2;
				case '"':
				case '\'':
					$text = $this->quotedString($c);
					$token = new Token(Token::STRING, $text, $location);
					break 2;
				case '$':
					$this->consume();
					$token = new Token(Token::REFERENCE, '$', $location);
					break 2;
				case '&':
					$this->consume();
					if ($this->get(0) === '&') {
						$this->consume();
						$token = new Token(Token::LOGICAL_AND, '&&', $location);
					}else {
						$token = new Token(Token::LOGICAL_AND, '&', $location);
					}
					break 2;
				case '|':
					$this->consume();
					if ($this->get(0) === '|') {
						$this->consume();
						$token = new Token(Token::LOGICAL_OR, '||', $location);
					}else {
						$token = new Token(Token::LOGICAL_OR, '|', $location);
					}
					break 2;
				case $this->EOF:
					$token = new Token(Token::END, '', $location);
					break 2;
				default:
					$text = $this->literal();
					if (\is_numeric($text)) {
						$token = new Token(Token::NUMBER, $text, $location);
					}else {
						$token = new Token(Token::STRING, $text, $location);
					}
					break 2;
			}

		}
		return $token;
	}

}
