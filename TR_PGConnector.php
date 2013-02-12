<?php
/**
 * PostgreSQL connector
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
class TR_PGConnector {
	protected $context;
	protected $error;
	protected $connected = false;
	
	function TR_PGConnector( $context ) {
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
		$db=pg_connect(
			"dbname=".$this->context->database.
			" host=".$this->context->host.
			" user=".$this->context->dbuser.
			" password=".$this->context->password);
			
		$this->connected = false;
	
		# $this->context->host, $this->context->dbuser, $this->context->password);		
			
		if (!$db) {
			$this->setError($this->context
				->getErrorMessage('trReport_noconnection',
					$this->context->dbuser,
					$this->context->host,pg_last_error()));	
			return FALSE;
		} 		
		
		if (!pg_dbname($db)) {
			$this->close($db);
			$this->setError($this->context->getErrorMessage('trReport_nodb'));
			return false;
		}

		$this->connected = true;
		
		return $db;
	}
	
	public function execute($sql,$db) {
		$result = pg_query($db,$sql);
		$errNo = pg_errno();
		$error = pg_error();
		if ($errNo != "") {
			$this->setError("PGSQL Error:".$errNo." - ".$error); 
		} else {
			$this->setError("");
		}
			
		return $result; 		
	}
	
	public function getRowCount($result) {
		return pg_num_rows($result);
	}
	
	public function getFieldCount($result) {
		return pg_num_fields($result);
	}
	
	public function seek($result, $row) {
		$rows = getRowCount($result);
		
		if ($rows > 0 and $row < $rows) {
			pg_data_seek( $result, $row);
		}
	}	
	
	/**
	* fetching meta data for a field
	*/
	public function getField($result, $fieldNum) {
		return pg_fetch_field($result, $fieldNum);
	}
	
	public function fetch($result) {
		return pg_fetch_array($result);
	}
	public function free($result) {
		pg_free_result($result);
	}
	
	public function close($db) {
		$this->connected = false;
		pg_close($db);		
	}

	public function getTable($table) {
		return $table;
	}
	
	public function setError($message) {
		$this->message=$message;
	}

	public function getError() {
		return $this->message;
	}

	public function getDbError($db) {
		return pg_last_error($db);
	}
}