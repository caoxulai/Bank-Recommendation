<?php

class File {
	public $f;
	
	function __construct($filename, $mode) {
		$this->f = fopen($filename, $mode) or die('Cannot open '.$filename);
	}
	
	function __destruct() {
		if ($this->f != NULL) fclose($this->f);
	}

}

?>