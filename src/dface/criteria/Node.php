<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria;

interface Node {

	function acceptNodeVisitor(NodeVisitor $visitor);

	function equals(Node $node);

}
