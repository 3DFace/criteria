<?php

namespace dface\criteria\builder;

class GettersGraphNavigator implements ObjectGraphNavigator
{

	/**
	 * @param $object
	 * @param $property_path_name
	 * @return mixed
	 */
	public function getValue($object, string $property_path_name)
	{
		$path = \explode('/', $property_path_name);
		$x = $object;
		foreach ($path as $p) {
			$getter = 'get'.\str_replace('_', '', \ucwords($p, '_'));
			if (\is_object($x) && \method_exists($x, $getter)) {
				$x = $x->$getter();
			}else {
				return null;
			}
		}
		return $x;
	}

}
