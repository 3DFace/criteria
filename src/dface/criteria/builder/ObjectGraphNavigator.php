<?php

namespace dface\criteria\builder;

interface ObjectGraphNavigator
{

	public function getValue($object, string $property_path_name);

}
