<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class GettersGraphNavigator implements ObjectGraphNavigator {

	function getValue($arrGraph, $propertyPathName){
		$path = explode('/', $propertyPathName);
		$x = $arrGraph;
		foreach($path as $p){
			$getter = 'get'.str_replace('_', '', ucwords($p, '_'));
			if(is_object($x) && method_exists($x, $getter)){
				$x = $x->$getter();
			}else{
				return null;
			}
		}
		return $x;
	}

}
