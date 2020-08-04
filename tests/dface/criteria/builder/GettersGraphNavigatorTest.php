<?php

namespace dface\criteria\builder;

use PHPUnit\Framework\TestCase;

class GettersGraphNavigatorTest extends TestCase
{

	public function testNavigator() : void
	{
		$graph = new Dummy(
			new Dummy(
				new Dummy(null, null, 1)
			),
			new Dummy(null, null, 1.23),
			'asd'
		);
		$nav = new GettersGraphNavigator();
		self::assertEquals('asd', $nav->getValue($graph, 'val'));
		self::assertEquals(1, $nav->getValue($graph, 'foo/foo/val'));
		self::assertEquals(1.23, $nav->getValue($graph, 'bar/val'));
		self::assertEquals(null, $nav->getValue($graph, 'bar/foo/val'));
	}

}
