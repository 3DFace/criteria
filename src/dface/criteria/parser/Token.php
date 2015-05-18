<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\criteria\parser;

class Token {

	public $location;
	public $type;
	public $text;

	function __construct($location, $type, $text){
		$this->location = $location;
		$this->type = $type;
		$this->text = $text;
	}

	function toString(){
		return $this->type.'('.$this->location.'){'.$this->text.'}';
	}

}
