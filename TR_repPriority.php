<?php
#EDITED: This file was modified to make the report function corectly.
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repPriority extends TR_Template{

	var $supportedCharts = array(
		"status" => array("parameter"=>"status", 
 	 					 "title"     =>"Test Case Status",
						 "tooltip"   =>"Test Case Status",
						 "alttext"   =>"Test Case Status"),
		"prio"   => array("parameter"=>"prio", 
 	 					 "title"     =>"Test Case Priorities",
						 "tooltip"   =>"Test Case Priorities",
						 "alttext"   =>"Test Case Priorities")
	);
	
	var $supportedChartTypes = array(
		"google"   => array( "bar", "pie", "pie3" ),
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

	function getReportFooter() {
		return "";
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
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_cases");
		$sql->addField("priority", "value", "Priority");
		$sql->addField("test_case_run_status", "name", "Status");
		$sql->addField("test_case_runs", "case_id", "Count", "count(distinct $1)");		
		$sql->addJoin("Inner", "=", "test_case_runs", "case_id", "test_cases", "case_id");
		$sql->addJoin("Inner", "=", "priority", "id", "test_cases", "priority_id");
		$sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");		
		$sql->addWhere("test_case_runs", "run_id", "=", $this->getRunID(), "");
$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");#EDITED: added this line to use only current build/environment for ca case run
		$sql->addGroupSort("Group", "priority", "value");#EDITED: changed Priority to priority for the sql sentance to be correct
		$sql->addGroupSort("Group", "test_case_run_status", "name"); #EDITED: added test_case_run_status as the field was ampty and no sorting was done.
		$sql->addGroupSort("Order", "priority", "value");
		
		$sqlStr = $sql->toSQL();
		return $sqlStr;
	}	
		
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$field = "";
		$color = "";
		switch ($chart) {
			case "status":
				$field="Status";
				$color="tcrs";
				break;
			case "prio"  : 
				$field="Priority";
				$color="prio";
				break;
			default:
				$field="Priority";
				$color="prio";			
		}

		if ($chart) {
			$this->getGoogleChartOneRowCount($google,$result, $type, $chart, $field, "Count", $color, "");
		} else {
			$this->getGoogleChartOneRowCount($google,$result, $type, "prio", $field, "Count", $color, "");
		}
	}
}

?>