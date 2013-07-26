<?php

class Encrypt {
	private $key = "MarcelaRamosBarragan";
	
	protected function makeHash ($salt, $pwd) {
		return $salt.":".hash("sha256",$this->key.$salt.$pwd);
	}
	
	public function makeSalt () {
		return bin2hex(openssl_random_pseudo_bytes(4, $cstrong));
	}
	
	public function makeSafe ($s) {
		$hex = $this->makeSalt();
		$p = $this->makeHash($hex, $s);
		return $p;
	}
	
	public function cookieSafe ($s, $u) {
		return $s."|".hash("sha256",$this->key.$s.$u);
	}
	
	public function check ($db, $post) {
		$salt = preg_split("/:/",$db);
		return $db == $this->makeHash($salt[0], $post);
	}
}


?>
