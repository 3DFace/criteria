<?php

namespace dface\criteria\parser;

use dface\criteria\node\Criteria;
use dface\criteria\node\Equals;
use dface\criteria\node\Greater;
use dface\criteria\node\GreaterOrEquals;
use dface\criteria\node\In;
use dface\criteria\node\IsNull;
use dface\criteria\node\Less;
use dface\criteria\node\LessOrEquals;
use dface\criteria\node\LogicalAnd;
use dface\criteria\node\LogicalNot;
use dface\criteria\node\LogicalOr;
use dface\criteria\node\MatchPattern;
use dface\criteria\node\MatchRegexp;
use dface\criteria\node\NotEquals;
use dface\criteria\node\NotMatch;
use dface\criteria\node\NotMatchRegexp;
use dface\criteria\node\NotNull;
use dface\criteria\node\Operand;
use dface\criteria\node\StringConstant;

class AnonymousParser extends AbstractParser
{

	private Operand $operand;
	private bool $allow_references;

	public function __construct(Lexer $lexer, bool $parse_numbers = false, bool $allow_references = false)
	{
		parent::__construct($lexer, $parse_numbers);
		$this->allow_references = $allow_references;
	}

	/**
	 * @param Operand $operand
	 * @param string $pattern
	 * @return Criteria|null
	 * @throws ParseException
	 */
	function parse(Operand $operand, string $pattern) : ?Criteria
	{
		$this->operand = $operand;
		$this->tokens = $this->lexer->explode($pattern);
		$this->index = 0;
		$this->count = \count($this->tokens);
		if ($this->getType(0) === Token::END) {
			return null;
		}
		return $this->parseOr();
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	protected function parseCriteria() : Criteria
	{
		$type = $this->getType(0);
		switch ($type) {
			case Token::NOT:
				return $this->parseNot();
			case Token::LEFT_BRACKET:
				return $this->parseBrackets();
			case Token::END:
				throw new ParseException('Unexpected end of input', $this->getToken(0)->location);
			case Token::EQUALS:
				return $this->parseEquals();
			case Token::NOT_EQUALS:
				return $this->parseNotEquals();
			case Token::MATCH:
				return $this->parseMatch();
			case Token::NOT_MATCH:
				return $this->parseNotMatch();
			case Token::MATCH_REGEXP:
				return $this->parseRegexp();
			case Token::NOT_MATCH_REGEXP:
				return $this->parseNotRegexp();
			case Token::GREATER:
				return $this->parseGreater();
			case Token::GREATER_OR_EQUALS:
				return $this->parseGreaterOrEquals();
			case Token::LESS:
				return $this->parseLess();
			case Token::LESS_OR_EQUALS:
				return $this->parseLessOrEquals();
			case Token::LEFT_SQUARE_BRACKET:
				return $this->parseIn();
			case Token::IS_NULL:
				return $this->parseIsNull();
			case Token::NOT_NULL:
				return $this->parseNotNull();
			case Token::STRING:
			case Token::NUMBER:
				$token = $this->getToken(0);
				$this->sureConsume($type);
				return new MatchPattern($this->operand, new StringConstant('%'.$token->text.'%'));
			default:
				$t = $this->getToken(0);
				throw new ParseException("Unexpected $t->type_name '$t->text' at $t->location", $t->location);
		}
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	private function parseNot() : Criteria
	{
		$this->sureConsume(Token::NOT);
		if ($this->getType(0) === Token::END) {
			return new LogicalAnd(array(
				new NotNull($this->operand),
				new NotEquals($this->operand, new StringConstant('')),
			));
		}
		return new LogicalNot($this->parseCriteria());
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	private function parseEquals() : Criteria
	{
		$this->sureConsume(Token::EQUALS);
		if ($this->getType(0) === Token::END) {
			return new LogicalOr(array(
				new IsNull($this->operand),
				new Equals($this->operand, new StringConstant('')),
			));
		}
		$c = $this->parseOperand($this->allow_references);
		return new Equals($this->operand, $c);
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	private function parseNotEquals() : Criteria
	{
		$this->sureConsume(Token::NOT_EQUALS);
		if ($this->getType(0) === Token::END) {
			return new LogicalAnd(array(
				new NotNull($this->operand),
				new NotEquals($this->operand, new StringConstant('')),
			));
		}
		$c = $this->parseOperand($this->allow_references);
		return new NotEquals($this->operand, $c);
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	private function parseGreater() : Criteria
	{
		$this->sureConsume(Token::GREATER);
		if ($this->getType(0) === Token::END) {
			return new LogicalAnd(array(
				new NotNull($this->operand),
				new Greater($this->operand, new StringConstant('')),
			));
		}
		$c = $this->parseOperand($this->allow_references);
		return new Greater($this->operand, $c);
	}

	/**
	 * @return GreaterOrEquals
	 * @throws ParseException
	 */
	private function parseGreaterOrEquals() : GreaterOrEquals
	{
		$this->sureConsume(Token::GREATER_OR_EQUALS);
		$c = $this->parseOperand($this->allow_references);
		return new GreaterOrEquals($this->operand, $c);
	}

	/**
	 * @return Less
	 * @throws ParseException
	 */
	private function parseLess() : Less
	{
		$this->sureConsume(Token::LESS);
		$c = $this->parseOperand($this->allow_references);
		return new Less($this->operand, $c);
	}

	/**
	 * @return LessOrEquals
	 * @throws ParseException
	 */
	private function parseLessOrEquals() : LessOrEquals
	{
		$this->sureConsume(Token::LESS_OR_EQUALS);
		$c = $this->parseOperand($this->allow_references);
		return new LessOrEquals($this->operand, $c);
	}

	/**
	 * @return MatchPattern
	 * @throws ParseException
	 */
	private function parseMatch() : MatchPattern
	{
		$this->sureConsume(Token::MATCH);
		$c = $this->parseOperand($this->allow_references);
		return new MatchPattern($this->operand, $c);
	}

	/**
	 * @return NotMatch
	 * @throws ParseException
	 */
	private function parseNotMatch() : NotMatch
	{
		$this->sureConsume(Token::NOT_MATCH);
		$c = $this->parseOperand($this->allow_references);
		return new NotMatch($this->operand, $c);
	}

	/**
	 * @return MatchRegexp
	 * @throws ParseException
	 */
	private function parseRegexp() : MatchRegexp
	{
		$this->sureConsume(Token::MATCH_REGEXP);
		$c = $this->parseOperand($this->allow_references);
		return new MatchRegexp($this->operand, $c);
	}

	/**
	 * @return NotMatchRegexp
	 * @throws ParseException
	 */
	private function parseNotRegexp() : NotMatchRegexp
	{
		$this->sureConsume(Token::NOT_MATCH_REGEXP);
		$c = $this->parseOperand($this->allow_references);
		return new NotMatchRegexp($this->operand, $c);
	}

	/**
	 * @return In
	 * @throws ParseException
	 */
	private function parseIn() : In
	{
		$set = [];
		$this->sureConsume(Token::LEFT_SQUARE_BRACKET);
		if ($this->getType(0) !== Token::RIGHT_SQUARE_BRACKET) {
			$set[] = $this->parseOperand($this->allow_references);
			while ($this->getType(0) !== Token::RIGHT_SQUARE_BRACKET) {
				$this->sureConsume(Token::COMA);
				$set[] = $this->parseOperand($this->allow_references);
			}
		}
		$this->sureConsume(Token::RIGHT_SQUARE_BRACKET);
		return new In($this->operand, $set);
	}

	/**
	 * @return IsNull
	 * @throws ParseException
	 */
	private function parseIsNull() : IsNull
	{
		$this->sureConsume(Token::IS_NULL);
		return new IsNull($this->operand);
	}

	/**
	 * @return NotNull
	 * @throws ParseException
	 */
	private function parseNotNull() : NotNull
	{
		$this->sureConsume(Token::NOT_NULL);
		return new NotNull($this->operand);
	}

}
