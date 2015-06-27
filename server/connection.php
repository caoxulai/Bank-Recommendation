<?php

// TODO: Use a LINQ PHP implementation
// TODO: Check column/table names for existence
class NMConnection {
	public $db;				// The opened database connection
	public $history;		// Past queries
	private $table;			// Name of current table
	private $prepared;

	function __construct($table) {
		// Class properties cannot evaluate or instantiate anything
		// The closest you can do is run something from the constructor
		// http://stackoverflow.com/questions/6866781/php-static-array-initialization-workaround

		// Connect to database
		$this->db = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
		if ($this->db->connect_errno) {
			$this->db->error = 'Failed to connect to MySQL: '.$this->db->connect_error;
			$this->db->history[] = 'Connecting to '.getenv('DB_NAME').' in '.getenv('DB_HOST');
			errorHandler();
		}

		// Prepared statements
		// http://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php
		//$this->prepared = array();

		$this->select($table);
	}

	function __destruct() {
		// Explicitly close prepared statements
		//$this->prepared[0]->close();

		// Close database connection
		$this->db->close();
	}

	function select($table) {
		$this->table = $table;
		// Bind table name to some prepared statements
		//$this->prepared[0]->bind_??('i', $some_int);
		return $this->db;
	}

	/**
	 * Takes an array of criteria to match rows against.
	 * A criterion is an array with column names as keys and value to match.
	 * If any of the criteria in the array matches the row,
	 * the row is considered matching
	 * An optional "request" array can be passed that includes columns to return
	 * @pre criteria is an array
	 */
	function getRows($criteria) {
        ////  when there are two input params, it seems not work properly
        
//        echo 'SSSS In get $$criteria = '.json_encode($criteria);
      if (count($criteria) == 0) return array();
      $rows = array();
      $cols = '*';
      $opt = array();

      // Make this a variadic function
      // Which means it has optional parameters
      if (func_num_args() > 1) {
        $opt = func_get_arg(1);
      }

      if (array_key_exists('request', $opt)) {
        $cols = '`'.implode('`,`', $opt['request']).'`';
      }

      $query = sprintf('SELECT %s FROM `%s` WHERE %s', $cols, $this->table, $this->dataToWhere($criteria));
//            echo 'SSSS final query = '.$query;
      if (array_key_exists('order_by',$opt)) {
        $query.= sprintf(" ORDER BY %s", $this->escape($opt['order_by'], true));
      }

      $this->history[] = $query;
      $result = $this->db->query($query);
      if (!$result) return $this->errorHandler();
      while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
      }
      $result->free();
      return $rows;
	}

	/**
	 * Return all the rows in a multidimensional array
	 */
	function getAllRows() {
		$rows = array();
		$orderBy = ($this->table == 'devices') ? ' ORDER BY `name`': '';
		$query = sprintf('SELECT * FROM `%s`%s', $this->table, $orderBy);
		$this->history[] = $query;
		$result = $this->db->query($query);
		if (!$result) return $this->errorHandler();
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		$result->free();
		return $rows;
	}

	/**
	 *
	 *
	 * @param array data Array of rows to add.
	 * Each row is an associative array mapping DB columns to values
	 */
	function addRows($data) {
		$query = '';
		foreach ($data as $row) {
			$usKeys = array_keys($row);
			$usVals = array_values($row);
            $sKeys = $sVals = array();
			foreach ($usKeys as &$usKey) {
				$sKeys[] = $this->escape($usKey, true);
			}
			foreach ($usVals as &$usVal) {
				$sVals[] = $this->escape($usVal);
			}
			// TODO: use single insert statement, with comma-separated values
			$query.= sprintf('INSERT INTO `%s` (`%s`) VALUES (%s);', $this->table, implode('`,`',$sKeys), implode(',',$sVals));
		}
		$this->history[] = $query;
		if ($this->db->query($query)) return true;
		else return $this->errorHandler();
	}

	function deleteRows($criteria) {
		if (count($criteria) == 0) return array();
		$query = sprintf('DELETE FROM `%s` WHERE %s', $this->table, $this->dataToWhere($criteria));
		$this->history[] = $query;
		if ($this->db->query($query)) return true;
		else return $this->errorHandler();
	}

	function deleteAllRows() {
		$query = sprintf('DELETE FROM `%s`', $this->table);
		$this->history[] = $query;
		if ($this->db->query($query)) return true;
		else return $this->errorHandler();
	}

	/**
	 * Replace each original row with new row
	 * @pre orig contains at least one unique key
	 * @pre orig is an array of criteria, see getRows
	 * @pre size of orig and new is the same, or new has one criteria in it
	 */
	function updateRows($orig, $new) {
		$query = '';

        echo 'SSSS  $orig = '.json_encode($orig);
        echo 'SSSS $new = '.json_encode($new);
		// new criteria applies to all that's matched if only one is given
		if (count($new) == 1 && count($orig) != 1) {
			for ($i = 0, $c = count($orig); $i < $c; $i++) {
				$new[$i] = $new[0];
			}
		}

		// Check for empty request
		if (empty($orig) && empty($new)) {
			$this->history[] = '';
			return true;
		}
        
        echo 'SSSS $c = '.count($new);
        $keys = array_keys($new);

        echo 'SSSS $keys = '.json_encode($keys);
        
        $separator = '';
        
		for ($i = 0, $c = count($new); $i < $c; $i++) {
			$set = '';
			           
            echo 'SSSS $keys[$i] = '.json_encode($keys[$i]);       
            echo 'SSSS $new[$keys[$i]] = '.json_encode($new[$keys[$i]]);
            
            $set.= sprintf($separator."`%s`=%s", $this->escape($keys[$i], true), $this->escape($new[$keys[$i]]));
            $separator = ',';
        }
        
        $query.= sprintf('UPDATE `%s` SET %s WHERE %s;',
				$this->table, $set, $this->dataToWhere(array($orig[0]))
				);
		
        
        echo 'SSSS final query = '.$query;
        
		$this->history[] = $query;
		if ($this->db->query($query)) return true;
		else return $this->errorHandler();
	}

	/**
	 * Takes an array of criteria to create a MySQL expression
	 * @pre criteria is an array of at least size 1
	 * @pre criteria is an array of criteria, see getRows
	 */
	private function dataToWhere($criteria) {
        
//                    echo 'SSSS$$criteria = '.json_encode($criteria);
		$separator1 = '';
		foreach ($criteria as $criterion) {
			$expr.= $separator1;
			$separator2 = '';
//            echo 'SSSS$criterion = '.json_encode($criterion);
			foreach ($criterion as $usKey => $usVal) {
				if (!empty($usKey) or !empty($usVal)) {
					// Escape every string input
					$sKey = $this->escape($usKey, true);
					$sVal = $this->escape($usVal);

					// Format each expression
					if ($usKey == NULL) {
						// When there's no specified column, use the value directly
						$expr.= sprintf($separator2.'%s', $usVal);
					} else if (!empty($sKey) and empty($sVal)) {
						$expr.= sprintf($separator2."`%s` = ''", $sKey);
					} else {
						$expr.= sprintf($separator2.'`%s` = %s', $sKey, $sVal);
					}
				}
				$separator2 = ' AND ';
			}// end of a criterion
			$separator1 = ' OR ';
		}
		return $expr;
	}

    /**
	 * @param mixed expr value to escape
     * [@param bool isKey escapes value without quoting if true]
     */
	private function escape($expr) {
		// Make this a variadic function
		// Which means it has optional parameters
		if (func_num_args() == 2) {
			// Quotes the value if it's a string and isKey is not true
			$isKey = func_get_arg(1);
			if (!is_bool($isKey)) $isKey = false;
		} else {
			$isKey = false;
		}

		if (is_string($expr)) {
			$expr = $this->db->escape_string($expr);
			if ($isKey) return $expr;
			else return "'".$expr."'";
		} else if (is_bool($expr)) {
			// MySQL uses 1 and 0 as boolean true and false
			if ($expr) return 1;
			else return 0;
		} else {
			return $expr;
		}
	}

	private function errorHandler() {
		$this->history[] = array_pop($this->history).': '.$this->db->error;
		return false;
	}
}

?>
