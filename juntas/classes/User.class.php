<?php
require_once("DB.class.php");
require_once("Encrypt.class.php");
require_once("GenTools.class.php");

class User {
	public $id, $name, $lastname, $email, $uname, $utype, $company, $fullData;
	
	private function makeData ($data) {
		$ret = array();
		foreach ($data as $n=>$v) {
			$ret [$n] = $v;
			if ($n == "name" || $n == "lastname") {
				$ret [$n] = mysql_real_escape_string($v);
			}
		}
		
		return $ret;
	}
	
	function __construct ($data) {
		$gt = new GenTools;
		$hash = new Encrypt;
		
		$this->id	    = $gt->setAndNotEmpty($data['ID']) 	     ? $data['ID']	   	   : "";
		$this->name 	= $gt->setAndNotEmpty($data['name']) 	 ? $data['name']	   : "";
		$this->lastname = $gt->setAndNotEmpty($data['lastname']) ? $data['lastname']   : "";
		$this->email 	= $gt->setAndNotEmpty($data['email'])	 ? $data['email']	   : "";
		$this->uname 	= $gt->setAndNotEmpty($data['uname'])	 ? $data['uname']	   : "";
		$this->utype 	= $gt->setAndNotEmpty($data['utype'])	 ? $data['utype']	   : "";
		$this->company 	= $gt->setAndNotEmpty($data['company'])  ? $data['company']    : "";
		$this->active   = $gt->setAndNotEmpty($data['active'])   ? $data['active']	   : "0";
		
		$this->fullData = $this->makeData(array(id=>$this->id,
										  		name=>$this->name,
										  		lastname=>$this->lastname,
										  		email=>$this->email,
										  		uname=>$this->uname,
										  		utype=>$this->utype,
										  		company=>$this->company,
										  		active=>$this->active));
		//var_dump ($this->fullData);
	}
	
	public function save ($pwd, $newPwd=NULL) {
		$gt = new GenTools;
		if (!$gt->setAndNotEmpty($pwd)) {
			return array(result=>"error", type=>"passwordNeeded", description=>"Please give me your password.");
		}
		$db = new DB;
		$encrypt = new Encrypt;
		$user = $db->select("tbl_users", array(uname=>$this->uname));
		if (!empty($user)) {
			if ($encrypt->check($user['pwd'], $pwd)&&$gt->setAndNotEmpty($newPwd)) {
				$this->id=$user['ID'];
				$user ['pwd'] = $encrypt->makeSafe($newPwd);
				return $db->update("tbl_users", $user, array(ID=>$user['ID']));;
			}else {
				return array(result=>"error", type=>"wrongPassword", description=>"Please give me your current password.");
			}
			if (!$gt->setAndNotEmpty($newPwd)) {
				return array(result=>"error", type=>"userExists", description=>"Please use another username.");
			}
		}else {
			//$fullData = $this->makeData ($this);
			$this->fullData['pwd'] = $encrypt->makeSafe($pwd);
			$this->id = $db->insert("tbl_users", $this->fullData);
			return $this->id;
		}
	}
}
?>