<?php
class DB {
	protected $db_name = 'animaes_juntas';
	protected $db_user = 'animaes_juntas';
	protected $db_pass = 'MdTaTxjhTzp5BMs8';
	protected $db_host = 'localhost';
	private $connection;
	
	function __construct ($name, $user, $pass, $host) {
		if ($name) $this->db_name = $name;
		if ($user) $this->db_user = $user;
		if ($pass) $this->db_pass = $pass;
		if ($host) $this->db_host = $host;
		$this->connect();
	}
	
	public function connect () {
		$this->connection = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
		mysql_select_db($this->db_name);
		//mysql_query("SET NAMES 'utf8'", $this->connection);
		return true;
	}
	
	private function processRowSet ($rowSet, $singleRow=false) {
		$resultArray = array();
		while ($row=mysql_fetch_assoc($rowSet)) {
			array_push($resultArray, $row);
		}
		if (count($resultArray) == 1) {
			if (count($resultArray[0]) == 1) {
				$col = array_keys ($resultArray[0]);
				return $resultArray[0][$col[0]];
			}
			return $resultArray[0];
		}
		
		return $resultArray;
	}
	
	public function select ($table, $conditions="", $cols="*" ) {
		$q = "SELECT $cols FROM $table";
		$conditionsStr = $this->stringifyKV (" AND ", $conditions);
		$q .= !empty($conditions)?" WHERE $conditionsStr":"";
		$result = mysql_query($q);
		
		return $this->processRowSet($result);
	}
	
	public function update ($table, $data, $conditions) {
		$set = $this->stringifyKV (", ", $data);
		$conditionsStr = $this->stringifyKV(" AND ", $conditions);
		
		$q = "UPDATE $table SET $set WHERE $conditionsStr";
		$result = mysql_query($q) or die (mysql_error());
		
		return $result;
	}
	
	public function insert ($table, $data) {
		$cols = array_keys($data);
		$vals = array_values($data);
		$colsStr = implode(", ", $cols);
		$valsStr = $this->stringify(", ", $vals);
		$q = "INSERT INTO $table ($colsStr) VALUES ($valsStr)";
		$result = mysql_query($q) or die($mysql_error());
		
		return mysql_insert_id();
	}
	
	public function delete ($table, $conditions) {
		$conditionsStr = $this->stringifyKV(" AND ", $conditions);
		$q = "DELETE FROM $table WHERE $conditionsStr";
		$result = mysql_query($q) or die(mysql_error());
		/*if ($table == "tbl_tokens" || $table == "tbl_sessions") {
			$this->housekeep($table);
		}*/
		//var_dump($q);
		return $result;
	}
	
	private function stringifyKV ($glue, $array) {
		$ret = array();
		foreach($array as $k=>$v) {
			$v = $this->isExpression($v)?$v:"'".$v."'";
			array_push($ret, "$k = $v");
		}
		return implode($glue, $ret);
	}
	
	private function stringify ($glue, $array) {
		$ret = array();
		foreach($array as $v) {
			$v = $this->isExpression($v)?$v:"'".$v."'";
			array_push($ret, $v);
		}
		return implode($glue, $ret);
	}
	
	private function isExpression ($v) {
		if (is_numeric($v) || preg_match("/^tbl_\w+\.+\w+$/", $v)) {
			return true;
		}
		return false;
	}
	
	protected function housekeep ($table) {
		$data = $this->select ($table);
		if ($data['ID']) {
			$this->update($table, array(ID=>1), array(ID=>$data['ID']));
			$i = 2;
		}else if ($data[0]['ID']) {
			for ($i = 0; $i < count($data); $i++) {
				$this->update($table, array(ID=>intval($i)+1), array(ID=>$data[$i]['ID']));
			}
		}
		$q = "ALTER TABLE $table AUTO_INCREMENT =".($i+1);
		$result = mysql_query($q) or die(mysql_error());
		return ($result);
	}
	
	function __destruct () {
		/*mysql_close($this->connection)*/;
	}
}

?>