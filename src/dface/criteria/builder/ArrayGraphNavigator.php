<?php

namespace dface\criteria\builder;

class ArrayGraphNavigator implements ObjectGraphNavigator
{

	/**
	 * @param array|object $object
	 * @param string $property_path_name
	 * @return array|int|string|mixed|null
	 */
	public function getValue($object, string $property_path_name)
	{
		$path = \explode('/', $property_path_name);
		$x = (array)$object;
		foreach ($path as $p) {
			if (!isset($x[$p])) {
				return null;
			}
			$x = $x[$p];
		}
		return $x;
	}

}
