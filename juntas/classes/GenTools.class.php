<?php

class GenTools {
	public function setAndNotEmpty ($var) {
		return isset($var)&&!empty($var);
	}
	
	public function put0s ($n, $length=2) {
		$str = strval($n);
		while (strlen($str)<$length) $str = "0".$str;
		return $str;
	}
}

?>