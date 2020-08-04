<?php

namespace dface\criteria\builder;

class GettersGraphNavigator implements ObjectGraphNavigator
{

	/**
	 * @param $arrGraph
	 * @param $propertyPathName
	 * @return mixed
	 */
	public function getValue($arrGraph, string $propertyPathName)
	{
		$path = \explode('/', $propertyPathName);
		$x = $arrGraph;
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
