<?php

namespace dface\criteria\builder;

use PHPUnit\Framework\TestCase;

class SimpleComparatorTest extends TestCase
{

	public function testSimpleComparator() : void
	{
		$c = new SimpleComparator();
		self::assertEquals(-1, $c->compare(10, 20));
		self::assertEquals(1, $c->compare(20, 10));
		self::assertEquals(0, $c->compare(20, 20));
		self::assertEquals(-1, $c->compare('a', 'b'));
		self::assertEquals(1, $c->compare('b', 'a'));
		self::assertEquals(0, $c->compare('a', 'a'));
		self::assertEquals(-1, $c->compare('2', 11));
		self::assertEquals(1, $c->compare('2a', 11));
		self::assertEquals(1, $c->compare('11', 3));
		self::assertEquals(-1, $c->compare('11a', 3));
		self::assertEquals(0, $c->compare('11', 11));
	}

}
