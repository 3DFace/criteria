<?php

namespace dface\criteria\parser;

use dface\criteria\node\Constant;
use dface\criteria\node\Criteria;
use dface\criteria\node\FloatConstant;
use dface\criteria\node\IntegerConstant;
use dface\criteria\node\LogicalAnd;
use dface\criteria\node\LogicalOr;
use dface\criteria\node\Operand;
use dface\criteria\node\Reference;
use dface\criteria\node\StringConstant;

abstract class AbstractParser
{

	protected Lexer $lexer;
	protected bool $parse_numbers;
	/** @var Token[] */
	protected array $tokens;
	protected int $count;
	protected int $index;

	function __construct(Lexer $lexer, bool $parse_numbers = false)
	{
		$this->lexer = $lexer;
		$this->parse_numbers = $parse_numbers;
	}

	protected function consume() : void
	{
		$this->index++;
	}

	/**
	 * @param $type
	 * @throws ParseException
	 */
	protected function sureConsume(int $type) : void
	{
		if ($this->getType(0) !== $type) {
			throw new ParseException(Token::typeName($type).' expected at '.$this->index, $this->index);
		}
		$this->consume();
	}

	protected function getType(int $i) : int
	{
		$j = $this->index + $i;
		return $j < $this->count ? $this->tokens[$j]->type : Token::END;
	}

	protected function getToken(int $i) : Token
	{
		$j = $this->index + $i;
		return $j < $this->count ? $this->tokens[$j] : new Token(Token::END, '', $this->count);
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	protected function parseOr() : Criteria
	{
		$members = [];
		$members[] = $this->parseAnd();
		while ($this->getType(0) === Token::LOGICAL_OR) {
			$this->consume();
			$members[] = $this->parseAnd();
		}
		if (!$members) {
			throw new ParseException('No OR members found');
		}
		return \count($members) > 1 ? new LogicalOr($members) : $members[0];
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	protected function parseAnd() : Criteria
	{
		$members = [];
		$members[] = $this->parseCriteria();
		while (true) {
			switch ($this->getType(0)) {
				case Token::LOGICAL_OR:
				case Token::END:
				case Token::RIGHT_BRACKET:
					break 2;
				case Token::LOGICAL_AND:
					$this->consume();
					break;
			}
			$members[] = $this->parseCriteria();
		}
		if (!$members) {
			throw new ParseException('No AND members found');
		}
		return \count($members) > 1 ? new LogicalAnd($members) : $members[0];
	}

	/**
	 * @param bool $allow_reference
	 * @return Operand
	 * @throws ParseException
	 */
	protected function parseOperand(bool $allow_reference) : Operand
	{
		$type = $this->getType(0);
		switch ($type) {
			case Token::STRING:
				return $this->parseString();
			case Token::NUMBER:
				return $this->parseNumber();
			case Token::REFERENCE:
				if (!$allow_reference) {
					$t = $this->getToken(0);
					$loc = $t->location;
					throw new ParseException("Reference is not allowed at $loc", $loc);
				}
				return $this->parseReference();
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException('Unexpected '.$t->type_name." '".$t->text."' at ".$loc, $loc);
		}
	}

	/**
	 * @return StringConstant
	 * @throws ParseException
	 */
	protected function parseString() : StringConstant
	{
		$token = $this->getToken(0);
		$this->sureConsume(Token::STRING);
		return new StringConstant($token->text);
	}

	/**
	 * @return Constant
	 * @throws ParseException
	 */
	protected function parseNumber() : Constant
	{
		$token = $this->getToken(0);
		$this->sureConsume(Token::NUMBER);
		if ($this->parse_numbers) {
			$int = \filter_var($token->text, FILTER_VALIDATE_INT);
			return $int !== false
				? new IntegerConstant($int)
				: new FloatConstant($token->text);
		}
		return new StringConstant($token->text);
	}

	/**
	 * @return Reference
	 * @throws ParseException
	 */
	protected function parseReference() : Reference
	{
		$this->sureConsume(Token::REFERENCE);
		$token = $this->getToken(0);
		$this->sureConsume(Token::STRING);
		return new Reference($token->text);
	}

	/**
	 * @return Criteria|LogicalOr|mixed
	 * @throws ParseException
	 */
	protected function parseBrackets()
	{
		$this->sureConsume(Token::LEFT_BRACKET);
		$criteria = $this->parseOr();
		$this->sureConsume(Token::RIGHT_BRACKET);
		return $criteria;
	}

	abstract protected function parseCriteria() : Criteria;

}
