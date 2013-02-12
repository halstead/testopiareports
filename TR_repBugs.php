<?php
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repBugs extends TR_Template{

	var $supportedCharts = array(
		"bstatus"  => array("parameter"=>"bstatus", 
						    "title"    =>"Bug Status",
						    "tooltip"  =>"Bug Status",
						    "alttext"  =>"Bug Status"),
		"prio"     => array("parameter"=>"prio", 
							"title"    =>"Bug Priorities",
							"tooltip"  =>"Bug Priorities",
							"alttext"  =>"Bug Priorities"),
		"severity" => array("parameter"=>"prio", 
							"title"    =>"Bug Severities",
							"tooltip"  =>"Bug Severities",
							"alttext"  =>"Bug Severities")
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
	
	/**
	* Create the report header
	**/
	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function getReportFooter() {
		return "";
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
		$this->setMsgNoResultsFound( "Testopia Reports: No bugs found for test run ".$this->getRunID());
		
		$con=$this->getConnector();

		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("bugs");
		$sql->addField("bugs", "bug_id", "ID");
		$sql->addField("bugs", "priority", "Priority");
		$sql->addField("bugs", "bug_severity", "Severity");
		$sql->addField("bugs", "bug_status", "Status");
		$sql->addField("bugs", "bug_severity", "Severity");
		$sql->addField("test_case_bugs", "case_id", "Test Case");
		$sql->addField("bugs", "short_desc", "Description");
		$sql->addJoin("Inner", "=", "test_case_bugs", "bug_id", "bugs", "bug_id");
		$sql->addJoin("Inner", "=", "test_case_runs", "case_run_id", "test_case_bugs", "case_run_id");		
		$sql->addWhere("test_case_runs", "run_id", "=", $this->getRunID());
$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"VERIFIED\"", "AND"); #EDITED: Added this line to rule out verified bugs
		$sql->addGroupSort("Order", "bugs", "priority");
		$sql->addGroupSort("Order", "bugs", "bug_status"); #EDITED: added this line to order by bug status as well.
		$sql->addGroupSort("Order", "bugs", "bug_id");
	

		return $sql->toSQL();
	}
	
	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		if ($type=="body") {
			switch ($field_name) {
				case "Test Case": 
						$output="<td><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
						break;
				case "ID"       :
						$output="<td><a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$value."\">".$value."</a></td>";
						break;
				case "Priority" :
						if ($value == "P1") {
							$output="<td class=\"testopia_Priority_P1\">".$value."</td>";
						}
				default			: return $output;
			}
		}
		return $output;
	}				
	
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$field = "";
		$color = "";
		$colorrange = "";
		switch ($chart) {
			case "bstatus":
				$field="Status";
				$color="bs";
				break;		
			case "prio": 
				$field="Priority";
				$color="prio";
				break;
			case "severity":
				$field="Severity";
				$color="auto";
				$colorrange="red";
				break;				
			default:
				$field="Priority";
				$color="prio";	
		}
	
		if ($chart) {
			$this->getGoogleChartOneRowCount($google, $result, $type, $chart, $field, "", $color, $colorrange);
		} else {
			$this->getGoogleChartOneRowCount($google,$result, $type, "prio", $field, "", $color, $colorrange);
		}
	}
}

?>