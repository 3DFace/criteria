<?php

namespace dface\criteria\builder;

interface Comparator
{

	public function compare($v1, $v2) : int;

}
