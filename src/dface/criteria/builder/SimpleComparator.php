<?php

namespace dface\criteria\builder;

class SimpleComparator implements Comparator
{

	function compare($v1, $v2) : int
	{
		if (\is_numeric($v1) && \is_numeric($v2)) {
			/** @noinspection TypeUnsafeComparisonInspection */
			if ($v1 == $v2) {
				return 0;
			}
			return ($v1 > $v2 ? 1 : -1);
		}
		return \max(-1, \min(1, \strcmp((string)$v1, (string)$v2)));
	}

}
