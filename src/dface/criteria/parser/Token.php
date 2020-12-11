<?php

namespace dface\criteria\parser;

class Token
{

	public const STRING = 1;
	public const NUMBER = 2;
	public const REFERENCE = 3;
	public const EQUALS = 4;
	public const NOT_EQUALS = 5;
	public const LESS = 6;
	public const LESS_OR_EQUALS = 7;
	public const GREATER = 8;
	public const GREATER_OR_EQUALS = 9;
	public const IS_NULL = 10;
	public const NOT_NULL = 11;
	public const MATCH = 12;
	public const NOT_MATCH = 13;
	public const MATCH_REGEXP = 14;
	public const NOT_MATCH_REGEXP = 15;
	public const NOT = 16;
	public const LEFT_BRACKET = 17;
	public const RIGHT_BRACKET = 18;
	public const LEFT_SQUARE_BRACKET = 19;
	public const RIGHT_SQUARE_BRACKET = 20;
	public const COMA = 21;
	public const LOGICAL_AND = 22;
	public const LOGICAL_OR = 23;
	public const END = 24;

	private const NAMES = [
		self::STRING => 'STRING',
		self::NUMBER => 'NUMBER',
		self::REFERENCE => 'REFERENCE',
		self::EQUALS => 'EQUALS',
		self::NOT_EQUALS => 'NOT_EQUALS',
		self::LESS => 'LESS',
		self::LESS_OR_EQUALS => 'LESS_OR_EQUALS',
		self::GREATER => 'GREATER',
		self::GREATER_OR_EQUALS => 'GREATER_OR_EQUALS',
		self::IS_NULL => 'IS_NULL',
		self::NOT_NULL => 'NOT_NULL',
		self::MATCH => 'MATCH',
		self::NOT_MATCH => 'NOT_MATCH',
		self::MATCH_REGEXP => 'MATCH_REGEXP',
		self::NOT_MATCH_REGEXP => 'NOT_MATCH_REGEXP',
		self::NOT => 'NOT',
		self::LEFT_BRACKET => 'LEFT_BRACKET',
		self::RIGHT_BRACKET => 'RIGHT_BRACKET',
		self::LEFT_SQUARE_BRACKET => 'LEFT_SQUARE_BRACKET',
		self::RIGHT_SQUARE_BRACKET => 'RIGHT_SQUARE_BRACKET',
		self::COMA => 'COMA',
		self::LOGICAL_AND => 'LOGICAL_AND',
		self::LOGICAL_OR => 'LOGICAL_OR',
		self::END => 'END',
	];

	public int $location;
	public int $type;
	public string $type_name;
	public string $text;

	public static function typeName(int $type) : string
	{
		return self::NAMES[$type] ?? 'UNKNOWN';
	}

	public function __construct(int $type, string $text, int $location)
	{
		$this->type = $type;
		$this->type_name = self::typeName($type);
		$this->text = $text;
		$this->location = $location;
	}

	public function __toString() : string
	{
		return $this->type_name.'('.$this->location.'){'.$this->text.'}';
	}

}
