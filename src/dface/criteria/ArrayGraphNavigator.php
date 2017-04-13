<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class ArrayGraphNavigator implements ObjectGraphNavigator {

	function getValue($arrGraph, $propertyPathName){
		$path = explode('/', $propertyPathName);
		$x = $arrGraph;
		foreach($path as $p){
			if(!isset($x[$p])){
				return null;
			}
			$x = $x[$p];
		}
		return $x;
	}

}
