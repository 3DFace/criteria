<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

class SimpleComparator implements Comparator {

	function compare($v1, $v2){
		if(is_numeric($v1) && is_numeric($v2)){
			if($v1 == $v2){
				return 0;
			}else{
				return ($v1 > $v2 ? 1 : -1);
			}
		}else{
			return max(-1, min(1, strcmp($v1, $v2)));
		}
	}

}
