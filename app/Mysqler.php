<?php
namespace Loader;

class Mysqler {
	public $connected;
	public $allowUpdate = true;

    /** @var \mysqli $mysqli */
    private $mysqli;
	
	private $preparedCache = array();
	
	public function __construct($db_host = NULL, $db_user = NULL, $db_pass = NULL, $db_name = NULL, $db_charset = 'utf8') {
		if (isset($db_host))
			$this->connect($db_host, $db_user, $db_pass, $db_name, $db_charset);
	}

	public function autocommit($value) {
		return $this->mysqli->autocommit($value);
	}
	
	public function commit() {
		return $this->mysqli->commit();
	}
	
	public function rollback() {
		return $this->mysqli->rollback();
	}
	
	public function connect($db_host, $db_user, $db_pass, $db_name, $db_charset = NULL) {
		if ($db_charset == NULL)
			$db_charset = "utf8";
		
		$this->mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
		
		if ($this->mysqli->connect_error) {
			throw new \Exception('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
		}
		
		$this->mysqli->set_charset($db_charset);
		
		$this->connected = true;
		
		return true;
	}

    /**
     * @param $query
     * @return \mysqli_result
     * @throws \Exception
     */
	public function query($query) {
		if ($this->connected) {
			$ret = $this->mysqli->query($query);
			if ($ret) {
				return $ret;
			} else {
				throw new \Exception("Failed to execute query. Error [{$this->mysqli->errno}]: {$this->mysqli->error}");
			}
		} else {
            throw new \Exception("Not connected to DB.");
		}
	}
	
	public function row($queryid) {
		if ($this->connected) {
			return $queryid->fetch_array();
		} else {
            throw new \Exception("Not connected to DB.");
		}
	}
	
	public function count($queryid) {
		if ($this->connected) {
			return $queryid->num_rows;
		} else {
            throw new \Exception("Not connected to DB.");
		}
	}
	
	public function insert($table, $data) {
		return $this->query("INSERT INTO $table (" . implode(',', array_keys($data)) . ") VALUES ('" . implode("','", $data) . "')");
	}
	
	public function prepare($query) {
		$prepared =  $this->mysqli->prepare($query);
		if (!$prepared) {
			throw new \Exception("Failed to prepare query: " . $this->mysqli->error . " ($query)");
		}
		return $prepared;
	}
	
	public function update($table, $where, $data) {
		$values = array();
		foreach ($data as $key => $value)
			$values[] = "$key = '$value'";

		return $this->query("UPDATE $table SET " . implode(',', $values) . " WHERE " . $where);
	}
	
	private function prepareCached($query) {
		$key = $query;
		
		if (isset($this->preparedCache[$key]))
			return $this->preparedCache[$key];
		
		$this->prepare($query);
		
		return $this->prepareCached[$key] = $this->prepare($query);
	}
	
	private function bindParams($prepared, $params) {
		return call_user_func_array(array($prepared, 'bind_param'), array_merge(
			array(str_repeat('s', count($params))),
			$this->refValues($params)
		));
	}
	
	private function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	
	public function updateCached($table, $where, $data) {
		
		$where_id = $where[0];
		$where_query = $where[1];
		$where_params = $where[2];
		
		$keys = array();
		$values = array();
		foreach ($data as $key => $value) {
			$keys[] = "$key = ?";
			$values[] = $value;
		}
		
		$query = $this->prepareCached("UPDATE $table SET " . implode(', ', $keys) . " WHERE $where_query");
		$this->bindParams($query, array_merge($values, $where_params));
		
		if (!$query->execute())
			throw new Exception("Failed to execute update.");
		
		$query->close();
		
		return true;
		
	}
	
	public function insertCached($table, $data) {
		
		$keys = array();
		$values = array();
		foreach ($data as $key => $value) {
			$keys[] = "?";
			$values[] = $value;
		}
		
		$str = "INSERT INTO $table (" . implode(',', array_keys($data)) . ") VALUES (" . implode(', ', $keys) . ")";
		$query = $this->prepareCached($str);
		$this->bindParams($query, $values);
		
		if (!$query->execute()) {
			throw new \Exception("Failed to execute insert.");
		}
		
		$query->close();
		
		return true;
		
	}
	
	public function insertOrUpdateCached($table, $where, $data) {
			
		$where_id = $where[0];
		$where_query = $where[1];
		$where_params = $where[2];
		
		$selection = $this->prepareCached("SELECT * FROM $table WHERE $where_query");
		$this->bindParams($selection, $where_params);

		if (!$selection->execute())
			throw new \Exception("Failed to execute selection query.");
		
		if ($selection->fetch()) {
			$selection->close();
			
			if ($this->allowUpdate)
				$this->updateCached($table, $where, $data);
			
			return false;
		}
		
		$selection->close();
		
		return $this->insertCached($table, $data);
	}
	
	public function insertOrUpdate($table, $where, $data) {
		
		if ($this->count($this->query("SELECT * FROM $table WHERE $where")) == 0) {
			$this->insert($table, $data);
			return $this->mysqli->insert_id;
		} else {
			if ($this->allowUpdate)
				$this->update($table, $where, $data);
			return false;
		}
	}
	
	public function insertId() {
		return $this->mysqli->insert_id;
	}
	
	public function getSingleRow($query) {
		$r = $this->row($this->query($query));
		return $r;
	}
	
	public function escape($string) {
        return $this->mysqli->real_escape_string($string);
    }
}
?>