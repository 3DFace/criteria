<?php

namespace dface\criteria\node;

interface Node
{

	public function acceptNodeVisitor(NodeVisitor $visitor);

}
