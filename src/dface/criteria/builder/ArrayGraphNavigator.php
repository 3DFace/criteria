<?php

namespace dface\criteria\builder;

class ArrayGraphNavigator implements ObjectGraphNavigator
{

	public function getValue($arrGraph, string $propertyPathName)
	{
		$path = \explode('/', $propertyPathName);
		$x = $arrGraph;
		foreach ($path as $p) {
			if (!isset($x[$p])) {
				return null;
			}
			$x = $x[$p];
		}
		return $x;
	}

}
