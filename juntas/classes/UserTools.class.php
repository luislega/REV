<?php

require_once("DB.class.php");
require_once("Encrypt.class.php");
require_once("GenTools.class.php");
require_once("User.class.php");

class UserTools {
	private $c, $s, $path="/intranet/juntas", $result;
	protected $series, $user;
	
	function __construct () {
		$this->c = explode("|",$_COOKIE['juntas_u_id']);
		if (!isset($_SESSION)) session_start();
		$this->s = $_SESSION;
	}
	
	public function login ($uname, $pwd, $remember=false) {
		$db = new DB;
		$gt = new GenTools;
		$user = $db->select("tbl_users", array(uname=>$uname), "tbl_users.ID, pwd, utype");
		if (!empty($user)) {
			$encrypt = new Encrypt;
			if ($encrypt->check($user['pwd'], $pwd)) {
				$this->series = $encrypt->makeSalt();
				$_SESSION['uname'] = $uname;
				$_SESSION['utype'] = $user['utype'];
				$_SESSION['session'] = $this->series;
				$db->delete("tbl_sessions", array(uname=>$uname));
				$db->insert("tbl_sessions", array(uname=>$uname, session=>$this->series));
				if ($remember==true) {
					$this->c = $this->makeAndSetCookie($uname);
					$db->insert("tbl_tokens", array(uname=>$this->c[0], series=>$this->c[1], token=>$this->c[2]));
				}
				$this->user = $this->get_by_id($user['ID']);
				$this->result = array(result=>"success", user=>$this->user, from=>"LOGIN", remembered=>$remember);
				return true;
			}
			$this->result = array(result=>"error", type=>"badPassword", description=>"wrong password");
			return false;
		}
		$this->result = array(result=>"error", type=>"badUser", description=>"wrong username");
		return false;
	}
	
	private function checkCookie () {
		$gt = new GenTools;
		$db = new DB;
		$encrypt = new Encrypt;
		if ($gt->setAndNotEmpty($this->c)) {
			$c = $this->c;
			$c_uname = $c[0];
			$c_series = $c[1];
			$c_token = $c[2];
			$token = $db->select("tbl_tokens", array(uname=>$c_uname, series=>$c_series), "ID, token");
			if (count($token)) {
				if ($c_token == $token['token']) {
					$newToken = $encrypt->makeSalt();
					$this->series = $c_series;
					$this->c = $this->makeAndSetCookie($c_uname);
					$db->update("tbl_tokens", array(token=>$this->c[2]), array(ID=>$token['ID']));
					$this->user = $this->get_by_uname($c_uname);
					$this->result = array(result=>"success", user=>$this->user, from=>"COOKIE");
					return true;
				}else {
					$db->update("tbl_tokens", array(token=>"CORRUPT"), array(uname=>$c_uname));
					$this->logout($c_uname);
					$this->result = array(result=>"error", type=>"corruptCookie", description=>"YOUR ACCOUNT HAS BEEN HACKED! You've been logged out of all your existing sessions. You'll have to reset your password on your next login attempt!");
					return false;
				}
			}
			$this->result = array(result=>"error", type=>"badCookie", description=>"Your cookie has expired. Login again.");
			return false;
		}
		$this->result = array(result=>"error", type=>"noCookie", description=>"Your cookie has expired. Login again.");
		return false;
	}
	
	private function checkSession () {
		$db = new DB;
		$gt = new GenTools;
		$s = $this->s;
		if ($gt->setAndNotEmpty($s['uname'])&&$gt->setAndNotEmpty($s['utype'])&&$gt->setAndNotEmpty($s['session'])) {
			$uname = $s['uname'];
			$utype = $s['utype'];
			$session = $s['session'];
			$db_session = $db->select("tbl_sessions", array(uname=>$uname), "session");
			if ($session == $db_session) {
				$this->user = $this->get_by_uname($uname);
				$this->result = array(result=>"success", user=>$this->user, from=>"SESSION");
				return true;
			}
			$this->result = array(result=>"error", type=>"badSession", desctiption=>"Your session was invalidated by a successful login from another device.");
			return false;
		}
		$this->result = array(result=>"error", type=>"noSession", desctiption=>"There is no active session on this device.");
		return false;
	}
	
	public function checkCredentials () {
		if ($this->checkCookie()) {
			return true;
		}
		if ($this->checkSession()) {
			return true;
		}
		return false;
	}
	
	public function logout ($uname="") {
		$db = new DB;
		$gt = new GenTools;
		unset($_SESSION['uname']);
		unset($_SESSION['utype']);
		unset($_SESSION['session']);
		unset($this->s);
		if ($gt->setAndNotEmpty($this->c)) {
			$c_uname = $this->c[0];
			$c_series = $this->c[1];
			$db->delete("tbl_tokens", array(uname=>$c_uname, series=>$c_series));
			$db->delete("tbl_sessions", array(uname=>$c_uname));
		}
		session_destroy();
		$this->c = NULL;
		return setcookie("juntas_u_id", "");
	}
	
	public function logoutAll ($uname) {
		$db = new DB;
		$result = $db->delete("tbl_tokens", array(uname=>$uname));
		$result = $db->delete("tbl_sessions", array(uname=>$uname));
		$this->logout();
		return $result;
	}
	
	public function logoutAllExceptThis ($uname) {
		$db = new DB;
		$gt = new GenTools;
		$c_restore = false;
		$s_restore = false;
		if ($this->checkCookie()) {
			$c_uname = $this->c[0];
			$c_restore = true;
		}
		if ($gt->setAndNotEmpty($_SESSION)) {
			$s_uname = $_SESSION['uname'];
			$s_utype = $_SESSION['utype'];
			$s_session = $_SESSION ['session'];
			$s_restore = true;
		}
		$this->logoutAll($uname);
		if ($c_restore) {
			$this_->c = $this->makeAndSetCookie($c_uname);
			$c = $this->c;
			$u_id = $db->insert("tbl_tokens", array(uname=>$c[0], series=>$c[1], token=>$c[2]));
			$this->user = $this->get_by_id($u_id);
		}
		if ($s_restore) {
			session_start();
			$_SESSION['uname'] = $s_uname;
			$_SESSION['utype'] = $s_utype;
			$_SESSION['session'] = $s_session;
			$this->s = $_SESSION;
			$db->connect();
			$db->insert("tbl_sessions", array(uname=>$s_uname, session=>$s_session));
			$this->user = $this->get_by_uname($s_uname);
		}
		return true;
	}
	
	public function getResult () {
		return $this->result;
	}
	
	public function get_by_id ($id) {
		$db = new DB;
		$user = new User ($db->select("tbl_users", array(ID=>$id), "ID, name, lastname, email, uname, utype, company"));
		return $user;
	}
	
	public function get_by_uname ($uname) {
		$db = new DB;
		$user = new User ($db->select("tbl_users", array(uname=>$uname), "ID, name, lastname, email, uname, utype, company"));
		return $user;
	}
	
	private function makeAndSetCookie ($u_name) {
		$h = new Encrypt;
		$cookieVal = $u_name."|".$this->series."|".$h->makeSalt();
		setcookie ("juntas_u_id", $cookieVal, time()+31536000, $this->path);
		return explode("|",$cookieVal);
	}
}

?>