<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SimpleComparatorTest extends \PHPUnit_Framework_TestCase {

	function testSimpleComparator(){
		$c = new SimpleComparator();
		$this->assertEquals(-1, $c->compare(10, 20));
		$this->assertEquals(1, $c->compare(20, 10));
		$this->assertEquals(0, $c->compare(20, 20));
		$this->assertEquals(-1, $c->compare('a', 'b'));
		$this->assertEquals(1, $c->compare('b', 'a'));
		$this->assertEquals(0, $c->compare('a', 'a'));
		$this->assertEquals(-1, $c->compare('2', 11));
		$this->assertEquals(1, $c->compare('2a', 11));
		$this->assertEquals(1, $c->compare('11', 3));
		$this->assertEquals(-1, $c->compare('11a', 3));
		$this->assertEquals(0, $c->compare('11', 11));
	}

}
