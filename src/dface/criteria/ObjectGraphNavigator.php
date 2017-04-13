<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

interface ObjectGraphNavigator {

	function getValue($object, $propertyPathName);

}
