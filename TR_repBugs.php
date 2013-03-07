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
		$sql->addField("bugs", "resolution", "Resolution");
#		$sql->addField("bugs", "bug_severity", "Severity");
		$sql->addField("test_case_bugs", "case_id", "Test_Case");
		$sql->addField("bugs", "short_desc", "Description");
		$sql->addField("bugs", "version", "Version");
		$sql->addField("bugs", "target_milestone", "Target_Milestone");
		$sql->addField("test_runs", "summary", "tr_date","SUBSTRING($1,1,10)");
		$sql->addField("bugs", "creation_ts", "bug_date","SUBSTRING($1,1,10)");
		$sql->addJoin("Inner", "=", "test_case_bugs", "bug_id", "bugs", "bug_id");
#		$sql->addJoin("Inner", "=", "test_case_runs", "case_id", "test_case_bugs", "case_id"); #EDITED: replace the below line with  this line to get bugs from all environments and builds
		$sql->addJoin("Inner", "=", "test_case_runs", "case_run_id", "test_case_bugs", "case_run_id");
		$sql->addJoin("Inner", "=", "test_runs", "run_id", "test_case_runs", "run_id");
#EDITED: The below block adds multiple runs report aggregatin and version filtering
$run_ids =  explode(", ", $this->getRunID());
$run_counter = 0;
$versions = explode(", ", $this->getVersions());
foreach ($run_ids as $run_id) {
foreach ($versions as $version) {
		if ($run_counter == 0) { # Check if this is the first parsing. If not, add "OR" to the sql sentence
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id);
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"VERIFIED\"", "AND"); #EDITED: Added this line to rule out verified bugs
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"RESOLVED\"", "AND"); #EDITED: Added this line to rule out resolved bugs
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"WONTFIX\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"INVALID\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"OBSOLETE\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"NOTABUG\"", "AND");
		$sql->addWhere("bugs", "version", "=", $version, "AND"); #EDITED: Added this line to filter by version if available
		$run_counter ++;
		}
		else {
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id, "OR");
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"VERIFIED\"", "AND"); #EDITED: Added this line to rule out verified bugs
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"RESOLVED\"", "AND"); #EDITED: Added this line to rule out resolved bugs
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"WONTFIX\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"INVALID\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"OBSOLETE\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"NOTABUG\"", "AND");
		$sql->addWhere("bugs", "version", "=", $version, "AND");

		}
}
}
		$sql->addGroupSort("Group", "bugs", "bug_id");
$sql_temp = $sql->toSQL();

#var_dump($sql_temp);
#exit;

#return $sql_temp;

return "SELECT table1.ID, table1.Priority, table1.Status, table1.Resolution, table1.Test_Case as \"Test Case\", table1.Description, table1.Version, table1.Target_Milestone as \"Target Milestone\", 
(IFNULL(TO_DAYS(table1.tr_date),0)-TO_DAYS(table1.bug_date)) as \"Age (days)\" from ($sql_temp) as table1";

#return $sql_temp;
	}

	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		if ($type=="body") {
			switch ($field_name) {
				case "Test Case": 
						$output="<td align=\"center\"><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
						break;
				case "ID"       :
						$output="<td align=\"center\"><a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$value."\">".$value."</a></td>";
						break;
				case "Age (days)":
						if ($value < "-700000") {
							$output = "<td align=\"center\">error</td>";
						} elseif ($value < "0") {
							$output ="<td align=\"center\"><FONT COLOR=\"#FF4000\">NEW</FONT></td>";
						} else {
							$output = "<td align=\"center\">".$value."</td>";
						}
						break;
				case "Description":
						$output= "<td align=\"left\">".$value."</td>";
						break;
				default			: return $output= "<td align=\"center\">".$value."</td>";
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