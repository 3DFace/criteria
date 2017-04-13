<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

interface Comparator {

	/**
	 * @param $v1
	 * @param $v2
	 * @return int
	 */
	function compare($v1, $v2);

}
