<?php
/*
Mysql connector
*/

/**
 * Copyright (C) 2008 - Ian Homer & bemoko
 *
 * This program is free software; you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) 
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for    
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along 
 * with this program; if not, see <http://www.gnu.org/licenses>.
 */
class TR_MysqlConnector {
	protected $context;
	protected $error;
	protected $connected = false;
	protected $db = null;
	
	function TR_MysqlConnector( $context ) {
		$this->setContext($context);
	}
	
	public function setContext($context) {
		$this->context=$context;
	}

	public function getContext() {
		return $this->context;
	}

	public function isConnected() {
		return $this->connected;
	}
	
	public function connect() {
		$this->db=mysql_connect($this->context->host, 
			$this->context->dbuser, $this->context->password);
		
		$this->connected = false;
		
		/*
		 * Set character encoding
		 */	
		
		mysql_query("SET NAMES '".$this->context->dbencoding."';", $this->db);
		mysql_query("SET CHARACTER SET '".$this->context->dbencoding."';", $this->db);
					
		if (!$this->db) {
			$this->setError($this->context
				->getErrorMessage('trReport_noconnection',
					$this->context->dbuser,
					$this->context->host,mysql_error()));	
			return false;
		} 		
		
		/*
		 * Test the connection early - note that we can't switch to the db
		 * with mysql_select_db since if this is a shared database connection
		 * with mediawiki then we will have changed the db for the mediawiki
		 * access.
		 */
		$sql="select count(id) from ".$this->context->database.
			".priority;";
		$result=mysql_query($sql,$this->db);
		if (!$result) {
			$this->setError($this->context->getErrorMessage('trReport_nodb',
				"Can't find test table 'priority' in database '".
				$this->context->database."' using ".$sql.
				" - this probably means your username and password set in the variable wgBugzillaReports are not correct."));
			$this->db=null;			
		} else if (mysql_error($this->db)) {
			$this->setError($this->context->getErrorMessage('trReport_nodb'),
				mysql_error($this->db));
			$this->db=null;
		} else if ($this->getRowCount($result) != 1) {
			$this->setError($this->context->getErrorMessage('trReport_nodb',
				$this->context->database."-".$this->getRowCount($result)));
			$this->db=null;
		}
		$this->free($result);
		
		$this->connected = true;
		return $this->connected;
	}
	
	public function execute($sql) {
		return mysql_query($sql, $this->db);		
	}
	
	public function getRowCount($result) {
		return mysql_num_rows($result);
	}
	
	public function getFieldCount($result) {
		return mysql_num_fields($result);
	}
	
	public function seek($result, $row) {
		$rows = $this->getRowCount($result);
		
		if ($rows > 0 and $row < $rows) {
			mysql_data_seek( $result, $row);
		}
	}
	
	/**
	* fetching meta data for a field
	*/
	public function getField($result, $fieldNum) {
		return mysql_fetch_field($result, $fieldNum);
	}
	
	public function fetch($result) {
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}
	public function free($result) {
		mysql_free_result($result);
	}
	
	public function close() {		
		/* 
		 * In PHP you should rely on script termination to close mysql
		 * and not explicitly call mysql_close($db) - see
		 * http://uk.php.net/manual/en/function.mysql-close.php
		 * This is because the implementation may reuse connections.  This 
		 * does happen if the connection details for the Bugzila database are
		 * the same as the wiki database.  Setting to null is good practice
		 * to free up the resource early.
		 */
		$this->connected = false;
		$this->db=null;
	}

	public function getTable($table) {
		return $this->context->database.".".$table;
	}
	
	public function setError($message) {
		$this->message=$message;
	}

	public function getError() {
		return $this->message;
	}
	
	public function getDbError() {
		return mysql_error($this->db);
	}

}