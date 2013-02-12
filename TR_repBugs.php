<?php

class TR_repBugs extends TR_Template{

	var $supportedCharts = array(
		"bstatus" => "bstatus",
		"prio"    => "prio"
	);
	
	var $supportedChartTypes = array(
		"google"   => array( "bar", "pie", "pie3" ),
		"ploticus" => array()
	);

	function getSupportedChartsArr() {
		return $this->supportedCharts;
	}
	
	function getSupportedChartTypesArr() {
		return $this->supportedChartTypes;
	}
	
	/**
	* Create the report header
	**/
	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function getReportName() {
		return "Bug Status Report";
	}		
	
	function getReport( $result ) {
		# check and set some parameters
		# field for calculating the total number; could be set to a field that is included in the field list of the select statement or to rowcount, which will simply count number of rows
		if ($this->getArgs()->get("total") == "true") {
			$this->getArgs()->set("total", "rowcount");
		}
		
		$output = $this->renderPlainHTML( $result );	
				
		#returning the output
	    return $output;
	}
	
	function getSQL() {		
		$con=$this->getConnector();

		$sql="";	
		$sql  ="SELECT ".$con->getTable("bugs").".bug_id as ID, ";
		$sql .= $con->getTable("bugs").".priority as Priority, ";
		$sql .= $con->getTable("bugs").".bug_severity as Severity, ";
		$sql .= $con->getTable("bugs").".bug_status as Status, ";
		$sql .= $con->getTable("test_case_bugs").".case_id as \"Test Case\", ";
		$sql .= $con->getTable("bugs").".short_desc as Description";
		$sql .= " FROM ".$con->getTable("bugs");
		$sql .= " INNER JOIN ".$con->getTable("test_case_bugs")." ON ".$con->getTable("bugs").".bug_id = ".$con->getTable("test_case_bugs").".bug_id";
		$sql .= " INNER JOIN ".$con->getTable("test_case_runs")." ON ".$con->getTable("test_case_bugs").".case_run_id = ".$con->getTable("test_case_runs").".case_run_id";
		$sql .= " WHERE ".$con->getTable("test_case_runs").".run_id = ".$this->getArgs()->get("run_id");
		$sql .= " ORDER BY Priority, ID";
		$sql .=";";		
		return $sql;
	}
	
	function renderCell($colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		switch ($field_name) {
			case "Test Case": 
					$output="<td><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
					break;
			case "ID"       :
					$output="<td><a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$value."\">".$value."</a></td>";
					break;
			case "Priority" :
					if ($value == "P1") {
						$output="<td style=\"background-color:#ff6666\">".$value."</td>";
					}
			default			: return $output;
		}
		return $output;
	}				
	
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		if ($chart) {
			$this->getGoogleChartOneRowCount($google, $result, $type, $chart);
		} else {
			$this->getGoogleChartOneRowCount($google,$result, $type, "prio");
		}
	}
	
	function getPloticusFileBasename() {
	}
	function getPloticusData( $result, $type ) {
	}	
}

?>