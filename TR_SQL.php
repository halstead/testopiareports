<?php

/**
 * Copyright (C) 2009 - Andreas Müller
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

class TR_SQL {
	
	private $connector;
	private $from;
	
	# data for constructing the SQL statement
	var $fields    = array();
	var $where     = array();
	var $joins     = array();
	var $groupsort = array();
	
	/**
	 *	Delete all contents for the SQL
	 */ 
	public function clear() {
		$fields    = array();
		$where     = array();
		$joins     = array();
		$groupsort = array();
		$from      = "";
	}
	
	public function setConnector( $connector ) {
		$this->connector = $connector;
	}
	
	public function setFrom( $from ) {
		$this->from = $from;
	}
	
	public function addField($table, $field, $alias="", $complex = "") {
		$newfield = array();
		$newfield["table"]   = $table;
		$newfield["field"]   = $field;
		$newfield["complex"] = $complex;
		$newfield["alias"] = $alias;
		
		$this->fields[] = $newfield;
	}
	
	public function addJoin($type, $operator, $table1, $field1, $table2, $field2) {		
		if (($type != "" or $operator != "") and $table1 != "" and $table2 != "" and $field1 != "" and $field2 != "") {
			$join = array();
			
			$join["type"]     = strtolower($type);
			$join["operator"] = $operator;
			$join["table1"]   = $table1;
			$join["field1"]   = $field1;
			$join["table2"]   = $table2;
			$join["field2"]   = $field2;
			
			$this->joins[] = $join;
		}
	}
	
	public function addWhere($table, $field, $operator, $value, $boolConnector = "") {
		if ($field != "" and $operator != "" and $value != "") {
			$wherep = array();
			
			$wherep["table"] 		 = $table;
			$wherep["field"] 		 = $field;
			$wherep["operator"] 	 = $operator;
			$wherep["value"] 		 = $value;
			$wherep["boolConnector"] = $boolConnector;
			$this->where[] = $wherep;
		}
	}
	
	public function addGroupSort($type, $table, $field) {
		if ($type != "" and $field != "" and $table != "") {
			$group = array();
			
			$group["type"]  = strtolower($type);
			$group["table"] = $table;
			$group["field"] = $field;		
			
			$this->groupsort[] = $group;
		}
	}
	
	public function toSQL() {
		$sql = "";
		
		$sql = "SELECT ";
		$fields = "";
		foreach ($this->fields as $field) {
			if ($fields != "") {
				$fields.=", ";
			}
			if ($field["complex"] == "") {
				$fields.=$this->connector->getTable($field["table"]).".".$field["field"];
			} else {
				$fields.=str_replace("$1", $this->connector->getTable($field["table"]).".".$field["field"], $field["complex"]);
			}
			if ($field["alias"] != "") {
				$fields.=" AS '".$field["alias"]."' ";
			}
		}
		$sql.=$fields;
		
		$sql.=" FROM ".$this->connector->getTable($this->from);
		
		foreach ($this->joins as $join) {
			switch ($join["type"]) {
				case "inner": $sql.=" INNER JOIN "; break;
				case "left" : $sql.=" LEFT JOIN "; break;
				case "right": $sql.=" INNER JOIN "; break;
				default     : $sql.=" INNER JOIN "; break;				
			}
			$sql.=$this->connector->getTable($join["table1"])." ON ";
			$sql.=$this->connector->getTable($join["table1"]).".".$join["field1"]." ".$join["operator"]." ".$this->connector->getTable($join["table2"]).".".$join["field2"];
		}

		if (empty($this->where) == false) {
			$sql.=" WHERE ";
			foreach ($this->where as $where) {
				if ($where["boolConnector"] != "") { 
					$sql.=" ".$where["boolConnector"]." ";
				}
				$sql.=$this->connector->getTable($where["table"]).".".$where["field"].$where["operator"].$where["value"];
			}		
		}
		
		if (empty($this->groupsort) == false) {
			switch ($this->groupsort[0]["type"]) {
				case "group": $sql.=" GROUP BY "; break;
				case "order": $sql.=" ORDER BY "; break;
				default     : $sql.=" ORDER BY "; break;
			}
			
			$fields="";
			foreach ($this->groupsort as $group) {
				if ($fields != "") {
					$fields.=", ";
				}
				$fields.=$this->connector->getTable($group["table"]).".".$group["field"];
			}		
			
			$sql.=$fields;
		}
		
		return $sql;
	}
}
 
?>