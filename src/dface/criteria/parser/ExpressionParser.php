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
use dface\criteria\node\LogicalNot;
use dface\criteria\node\MatchPattern;
use dface\criteria\node\MatchRegexp;
use dface\criteria\node\NotEquals;
use dface\criteria\node\NotMatch;
use dface\criteria\node\NotMatchRegexp;
use dface\criteria\node\NotNull;
use dface\criteria\node\Operand;

class ExpressionParser extends AbstractParser
{

	/**
	 * @param string $pattern
	 * @return Criteria|null
	 * @throws ParseException
	 */
	public function parse(string $pattern) : ?Criteria
	{
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
			default:
				return $this->parseExpression();
		}
	}

	/**
	 * @return Criteria
	 * @throws ParseException
	 */
	private function parseExpression()
	{
		$left = $this->parseOperand(true);
		$type = $this->getType(0);
		switch ($type) {
			case Token::EQUALS:
				return $this->parseEquals($left);
			case Token::NOT_EQUALS:
				return $this->parseNotEquals($left);
			case Token::MATCH:
				return $this->parseMatch($left);
			case Token::NOT_MATCH:
				return $this->parseNotMatch($left);
			case Token::MATCH_REGEXP:
				return $this->parseRegexp($left);
			case Token::NOT_MATCH_REGEXP:
				return $this->parseNotRegexp($left);
			case Token::GREATER:
				return $this->parseGreater($left);
			case Token::GREATER_OR_EQUALS:
				return $this->parseGreaterOrEquals($left);
			case Token::LESS:
				return $this->parseLess($left);
			case Token::LESS_OR_EQUALS:
				return $this->parseLessOrEquals($left);
			case Token::LEFT_SQUARE_BRACKET:
				return $this->parseIn($left);
			case Token::IS_NULL:
				return $this->parseIsNull($left);
			case Token::NOT_NULL:
				return $this->parseNotNull($left);
			default:
				$t = $this->getToken(0);
				$loc = $t->location;
				throw new ParseException('Unexpected '.$t->type_name." '".$t->text."' at ".$loc, $loc);
		}
	}

	/**
	 * @return LogicalNot
	 * @throws ParseException
	 */
	private function parseNot() : LogicalNot
	{
		$this->sureConsume(Token::NOT);
		return new LogicalNot($this->parseCriteria());
	}

	/**
	 * @param Operand $left
	 * @return Equals
	 * @throws ParseException
	 */
	private function parseEquals(Operand $left) : Equals
	{
		$this->sureConsume(Token::EQUALS);
		$right = $this->parseOperand(true);
		return new Equals($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return NotEquals
	 * @throws ParseException
	 */
	private function parseNotEquals(Operand $left) : NotEquals
	{
		$this->sureConsume(Token::NOT_EQUALS);
		$right = $this->parseOperand(true);
		return new NotEquals($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return Greater
	 * @throws ParseException
	 */
	private function parseGreater(Operand $left) : Greater
	{
		$this->sureConsume(Token::GREATER);
		$right = $this->parseOperand(true);
		return new Greater($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return GreaterOrEquals
	 * @throws ParseException
	 */
	private function parseGreaterOrEquals(Operand $left) : GreaterOrEquals
	{
		$this->sureConsume(Token::GREATER_OR_EQUALS);
		$right = $this->parseOperand(true);
		return new GreaterOrEquals($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return Less
	 * @throws ParseException
	 */
	private function parseLess(Operand $left) : Less
	{
		$this->sureConsume(Token::LESS);
		$right = $this->parseOperand(true);
		return new Less($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return LessOrEquals
	 * @throws ParseException
	 */
	private function parseLessOrEquals(Operand $left) : LessOrEquals
	{
		$this->sureConsume(Token::LESS_OR_EQUALS);
		$right = $this->parseOperand(true);
		return new LessOrEquals($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return MatchPattern
	 * @throws ParseException
	 */
	private function parseMatch(Operand $left) : MatchPattern
	{
		$this->sureConsume(Token::MATCH);
		$right = $this->parseOperand(true);
		return new MatchPattern($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return NotMatch
	 * @throws ParseException
	 */
	private function parseNotMatch(Operand $left) : NotMatch
	{
		$this->sureConsume(Token::NOT_MATCH);
		$right = $this->parseOperand(true);
		return new NotMatch($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return MatchRegexp
	 * @throws ParseException
	 */
	private function parseRegexp(Operand $left) : MatchRegexp
	{
		$this->sureConsume(Token::MATCH_REGEXP);
		$right = $this->parseOperand(true);
		return new MatchRegexp($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return NotMatchRegexp
	 * @throws ParseException
	 */
	private function parseNotRegexp(Operand $left) : NotMatchRegexp
	{
		$this->sureConsume(Token::NOT_MATCH_REGEXP);
		$right = $this->parseOperand(true);
		return new NotMatchRegexp($left, $right);
	}

	/**
	 * @param Operand $left
	 * @return In
	 * @throws ParseException
	 */
	private function parseIn(Operand $left) : In
	{
		$set = [];
		$this->sureConsume(Token::LEFT_SQUARE_BRACKET);
		if ($this->getType(0) !== Token::RIGHT_SQUARE_BRACKET) {
			$set[] = $this->parseOperand(true);
			while ($this->getType(0) !== Token::RIGHT_SQUARE_BRACKET) {
				$this->sureConsume(Token::COMA);
				$set[] = $this->parseOperand(true);
			}
		}
		$this->sureConsume(Token::RIGHT_SQUARE_BRACKET);
		return new In($left, $set);
	}

	/**
	 * @param Operand $left
	 * @return IsNull
	 * @throws ParseException
	 */
	private function parseIsNull(Operand $left) : IsNull
	{
		$this->sureConsume(Token::IS_NULL);
		return new IsNull($left);
	}

	/**
	 * @param Operand $left
	 * @return NotNull
	 * @throws ParseException
	 */
	private function parseNotNull(Operand $left) : NotNull
	{
		$this->sureConsume(Token::NOT_NULL);
		return new NotNull($left);
	}

}
