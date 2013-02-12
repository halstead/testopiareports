<?php

class TR_repPriority extends TR_Template{

	var $supportedCharts = array(
		"status" => "status",
		"prio"   => "prio"
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

	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function getReportName() {
		return "Priority Breakdown Report";
	}
	
	function getReport( $result ) {
		# check and set some parameters
		# field for calculating the total number; could be set to a field that is included in the field list of the select statement or to rowcount, which will simply count number of rows
		if ($this->getArgs()->get("total") == "true") {
			$this->getArgs()->set("total", "Count");
		}
		
		$output = $this->renderPlainHTML( $result );
		
		#returning the output
		return $output;
	}
	
	function getSQL() {		
		$con=$this->getConnector();
		
		$sql="";	
		$sql ="SELECT ".$con->getTable("priority").".value as Priority, ".$con->getTable("test_case_run_status").".name as Status,";
		$sql.="count(distinct ".$con->getTable("test_case_runs").".case_id) as Count ";
		$sql.=" FROM ".$con->getTable("test_cases"); 
		# link the test cases to the run
		$sql.=" INNER JOIN ".$con->getTable("test_case_runs"); 
		$sql.=" ON ".$con->getTable("test_cases").".case_id = ".$con->getTable("test_case_runs").".case_id";
		# get value for priority
		$sql.=" INNER JOIN ".$con->getTable("priority");
		$sql.=" ON ".$con->getTable("test_cases").".priority_id = ".$con->getTable("priority").".id";
		# get value for test case run status
		$sql.=" INNER JOIN ".$con->getTable("test_case_run_status");
		$sql.=" ON ".$con->getTable("test_case_runs").".case_run_status_id = ".$con->getTable("test_case_run_status").".case_run_status_id";
		
		$runID = $this->getRunID();
		if ($runID) {
			$sql.=" WHERE run_id = ".$runID;
			$sql.=" GROUP BY value, name";
		} else {
			$sql.=" GROUP BY run_id, value, case_run_status_id";
		}
		
		$sql.=" ORDER BY Priority";
		$sql.=";";		
		return $sql;
	}

	function renderCell($colNo, $field_name, $value, $lineNo, $line) {
		return "";
	}		
		
	function getPloticusFileBasename() {
	}
	function getPloticusData( $result, $type ) {
	}
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		if ($chart) {
			$this->getGoogleChartOneRowCount($google,$result, $type, $chart);
		} else {
			$this->getGoogleChartOneRowCount($google,$result, $type, "prio");
		}
	}
}

?>