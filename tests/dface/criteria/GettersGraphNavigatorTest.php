<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class GettersGraphNavigatorTest extends \PHPUnit_Framework_TestCase {

	function testNavigator(){
		$graph = new Dummy(
			new Dummy(
				new Dummy(null, null, 1)
			),
			new Dummy(null, null, 1.23),
			'asd'
		);
		$nav = new GettersGraphNavigator();
		$this->assertEquals('asd', $nav->getValue($graph, 'val'));
		$this->assertEquals(1, $nav->getValue($graph, 'foo/foo/val'));
		$this->assertEquals(1.23, $nav->getValue($graph, 'bar/val'));
		$this->assertEquals(null, $nav->getValue($graph, 'bar/foo/val'));

	}

}
