<?php
	defined('_SECURITY') or die("Access denied.");
	
	/**
	 * Connect and manipulate the database
	 * 
	 * @var $sql
	 *   SQL query string
	 * @var $tablePrefix
	 *   Prefix for tables in the database
	 * @var $resource
	 *   Handle of the connection
	 * @var $cursor
	 *   Result after execute queries
	 */
	class Database {
		private $sql = '';
		private $resource = null;
		private $cursor = null;
		
		/**
		 * Create a new connection
		 * 
		 * @param $host
		 *   Host where the database on
		 * @param $user
		 *   Username you use to connect
		 * @param $pass
		 *   Password you use to connect
		 * @param $db
		 *   Name of the database
		 * @param $table_prefix
		 *   Prefix for tables
		 */
		function database($host, $user, $pass, $db) {
			$this->_resource = mysql_pconnect($host, $user, $pass) or die("Bad connection.<br />\nPlease check your host, username or password.");
			mysql_select_db($db, $this->_resource) or die("Database not found.");
			
			$this->query("SET NAMES utf8");
			$this->query("SET CHARACTER SET utf8");
			$this->query("SET COLLATION_CONNECTION = utf8_general_ci");
		}
		
		/**
		 * Check whether the parameter is a correct integer (Prevent SQL Injection)
		 * 
		 * @param $number
		 *   The parameter you want to check
		 * @return
		 *   True or false
		 */
		function checkInt($number) {
			if ((string)(int)$number == (string)$number) return $number;
			else die("Incorrect parameter");
		}
		
		/**
		 * Check whether the parameter is a correct float (Prevent SQL Injection)
		 * 
		 * @param $number
		 *   The parameter you want to check
		 * @return
		 *   True or false
		 */
		function checkFloat($number) {
			if ((string)(float)$number == (string)$number) return $number;
			else die("Incorrect parameter.");
		}
		
		/**
		 * Process a string (Prevent SQL Injection)
		 * 
		 * @param $text
		 *   Content of the string
		 * @return
		 *   The good string
		 */
		function checkString($text) {
			return "'" . str_replace('\'', '\\\'', str_replace('\\', '\\\\', $text)) . "'";
		}
		
		/**
		 * Replate the symbol (#__) with the prefix of the tables
		 * 
		 * @param $sql
		 *   SQL query
		 * @param $prefix
		 *   The symbol
		 * @return
		 *   The good query
		 */
		function replacePrefix($sql, $prefix = '#__') {
			$sql = trim($sql);
			
			$escaped = false;
			$quoteChar = '';
			
			$n = strlen($sql);
			
			$startPos = 0;
			$literal = '';
			while ($startPos < $n) {
				$ip = strpos($sql, $prefix, $startPos);
				if ($ip === false) break;
				
				$j = strpos($sql, "'", $startPos);
				$k = strpos($sql, "'", $startPos);
				if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
					$quoteChar = "'";
					$j = $k;
				}
				else $quoteChar	= "'";
				
				if ($j === false) $j = $n;
				
				$startPos = $j;
				
				$j = $startPos + 1;
				
				if ($j >= $n) break;
				
				while (true) {
					$k = strpos($sql, $quoteChar, $j);
					$escaped = false;
					if ($k === false) break;
					$l = $k - 1;
					while ($l >= 0 && $sql{$l} == "\\") {
						$l--;
						$escaped = !$escaped;
					}
					if ($escaped) {
						$j = $k + 1;
						continue;
					}
					break;
				}
				if ($k === false) {
					// Error in SQL query
					break;
				}
					
				$literal .= substr($sql, $startPos, $k - $startPos + 1);
				$startPos = $k + 1;
			}
			if ($startPos < $n) {
				$literal .= substr($sql, $startPos, $n - $startPos);
			}
				
			return $literal;
		}
		
		/**
		 * Parse a query
		 * @param $sql
		 *   SQL query
		 * @param $args
		 *   The array of parameters
		 * @return
		 *   The good query
		 */
		function parseArgument($sql, $args = array()) {
			
			$temp = '';
			$offset = 0;
			$i = 0;
			while (($pos = strpos($sql, '%', $offset)) !== false) {
				$temp .= substr($sql, $offset, $pos - $offset);
				switch ($sql[$pos + 1]) {
					case 'd':
						$temp .= $this->checkInt($args[$i]);
						$i++;
						break;
					case 'f':
						$temp .= $this->checkFloat($args[$i]);
						$i++;
						break;
					case 's':
						$temp .= $this->checkString($args[$i]);
						$i++;
						break;
					default:
						$temp .= '%';
						if ($sql[$pos + 1] != '%') $pos = $pos - 1;
				}
				$offset = $pos + 2;
			}
			$temp .= substr($sql, $offset);
			return $temp;
		}
		
		/**
		 * Receive and process SQL query, return all results
		 *  		 
		 * @param $sql
		 *   SQL query		
		 *  @return
		 *   The result of this SQL query
		 */
		function query($sql) {
			$args = func_get_args();
			array_shift($args);
			$this->_sql = $this->replacePrefix($this->parseArgument($sql, $args));
			$this->_cursor = mysql_query($this->_sql, $this->_resource);
			return $this->_cursor;
		}
		
		/**
		 * Get the previous query
		 * 
		 * @return
		 *   Content of the query
		 */
		function getQuery() {
			return htmlspecialchars($this->_sql);
		}
		
		/**
		 * Get the number of rows after execute the query
		 * 
		 * @return
		 *   Number of rows
		 */
		function getNumRows() {
			if (!$this->_cursor) return false;
			return mysql_num_rows($this->_cursor);
		}
		
		/**
		 * Get the number of affected rows after execute the query
		 * 
		 * @return
		 *   Number of rows
		 */
		function getAffectedRows() {
			if (!$this->_resource) return false;
			return mysql_affected_rows($this->_resource);
		}
		
		/**
		 * Check whether none was be returned
		 * 
		 * @return
		 *   True or false
		 */
		function noRow() {
			return (!$this->getNumRows());
		}
		
		/**
		 * Check whether none was be affected by UPDATE, DELETE or INSERT query
		 * 
		 * @return
		 *   True or false
		 */
		function noAffected() {
			return (!$this->getAffectedRows());
		}
		
		/**
		 * Fetch result rows from the previous query as an array.
		 * 
		 * @return
		 *   The result array
		 */
		function getList() {
			$arr = array();
			if (!$this->_cursor) return $arr;
			while ($row = mysql_fetch_assoc($this->_cursor)) $arr[] = $row;
			mysql_free_result($this->_cursor);
			return $arr;
		}

		/**
		 * Return an individual result row from the previous query.
		 * 
		 * @return
		 *   The result row
		 */
		function getRow() {
			if (!$this->_cursor) return array();
			$val = mysql_fetch_assoc($this->_cursor);
			mysql_free_result($this->_cursor);
			return $val;
		}
	}