<?php
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repAgenda extends TR_Template{

#	var $supportedCharts = array(
#		"bstatus"  => array("parameter"=>"bstatus", 
#						    "title"    =>"Bug Status",
#						    "tooltip"  =>"Bug Status",
#						    "alttext"  =>"Bug Status"),
#		"prio"     => array("parameter"=>"prio", 
#							"title"    =>"Bug Priorities",
#							"tooltip"  =>"Bug Priorities",
#							"alttext"  =>"Bug Priorities"),
#		"severity" => array("parameter"=>"prio", 
#							"title"    =>"Bug Severities",
#							"tooltip"  =>"Bug Severities",
#							"alttext"  =>"Bug Severities")
#	);
#	
#	var $supportedChartTypes = array(
#		"google"   => array( "bar", "pie", "pie3" ),
#	);

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
		return "Test Run Agenda";
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

$run_ids =  explode(", ", $this->getRunID());
$run_counter = 0;
foreach ($run_ids as $run_id) {


		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		
		$sql->setFrom("test_case_runs");
		$sql->addField("test_plans", "name", "Test_Plan");
                $sql->addField("test_environments", "name", "Environment");
		$sql->addField("test_case_runs", "case_id", "Test_Case");
		$sql->addField("test_cases","summary", "Summary");
		$sql->addField("test_case_categories","name", "Category");
		$sql->addField("test_case_run_status", "name", "Status");
		$sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_case_runs", "environment_id");
		$sql->addJoin("Inner", "=", "test_runs", "run_id", "test_case_runs", "run_id");
		$sql->addJoin("Inner", "=", "test_plans", "plan_id", "test_runs", "plan_id");
		$sql->addJoin("Inner", "=", "test_cases", "case_id", "test_case_runs", "case_id");
		$sql->addJoin("Inner", "=", "test_case_categories", "category_id", "test_cases", "category_id");
		$sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id,"");
		$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND"); # EDITED: added this line to select only current build and environment case runs


$sqlStr=$sql->toSQL();
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector()); 
		$sql->setFrom("bugs"); 
		$sql->addField("bugs", "bug_id", "ID", "GROUP_CONCAT(DISTINCT $1)");#EDITED: This basically is for removing duplicate bug entries
		$sql->addField("bugs", "bug_status", "Status"); 
#		$sql->addField("bugs", "bug_severity", "Severity"); 
		$sql->addField("test_case_bugs", "case_id", "Test_Case"); 
#		$sql->addField("bugs", "short_desc", "Description"); 
		$sql->addField("bugs", "version", "Version");
		$sql->addJoin("Inner", "=", "test_case_bugs", "bug_id", "bugs", "bug_id"); 
#		$sql->addJoin("Inner", "=", "test_case_runs", "case_id", "test_case_bugs", "case_id"); #EDITED: replace the below line with  this line to get bugs from all environments and builds
		$sql->addJoin("Inner", "=", "test_case_runs", "case_run_id", "test_case_bugs", "case_run_id");
$version_counter = 0;
$versions = explode(", ", $this->getVersions());
foreach ($versions as $version) {
		if ($version_counter == 0) { # Check if this is the first parsing. If not, add "OR" to the sql sentence
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id);
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"VERIFIED\"", "AND"); #EDITED: Added this line to rule out verified bugs
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"RESOLVED\"", "AND"); #EDITED: Added this line to rule out resolved bugs
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"WONTFIX\"", "AND");
		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"INVALID\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"OBSOLETE\"", "AND");
		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"NOTABUG\"", "AND");
		$sql->addWhere("bugs", "version", "=", $version, "AND"); #EDITED: Added this line to filter by version if available
		$version_counter ++;
		}
		else {
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id, "OR");
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"VERIFIED\"", "AND"); #EDITED: Added this line to rule out verified bugs
#		$sql->addWhere("bugs", "bug_status", " NOT LIKE ", "\"RESOLVED\"", "AND"); #EDITED: Added this line to rule out resolved bugs
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"WONTFIX\"", "AND");
		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"INVALID\"", "AND");
#		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"OBSOLETE\"", "AND");
		$sql->addWhere("bugs", "resolution", " NOT LIKE ", "\"NOTABUG\"", "AND");
		$sql->addWhere("bugs", "version", "=", $version, "AND");
		}
}

		$sql->addGroupSort("Group", "test_case_bugs", "case_id");
if (strpos($this->getRunID(), ",") !== FALSE) { # Insert a RUN_ID column if multiple test runs are present. This is set so user can identify identical test cases in different test runs.
		$bonus_sql = "$run_id as \"Test Run\", table1.Test_Plan, table1.Environment, ";
		}
		else {
		$bonus_sql = "";
		}
$sqlStr2=$sql->toSQL();
if ($run_counter == 0) { #Check if this is the first parsing. If not, add "UNION ALL" to the sql sentence
	$result = "SELECT $bonus_sql table1.Test_Case as \"Test Case\", table1.Summary, table1.Category, table1.Status, GROUP_CONCAT(table2.ID) \"Bugs\" FROM ($sqlStr) AS table1 LEFT JOIN ($sqlStr2) as table2 ON table2.Test_Case = table1.Test_Case GROUP BY table1.Test_Case";
	$run_counter++;
	}
else {
	$result .= " UNION ALL SELECT $bonus_sql table1.Test_Case as \"Test Case\", table1.Summary, table1.Category, table1.Status, GROUP_CONCAT(table2.ID) \"Bugs\" FROM ($sqlStr) AS table1 LEFT JOIN ($sqlStr2) as table2 ON table2.Test_Case = table1.Test_Case GROUP BY table1.Test_Case";
	}
}
return $result;


	}
	

        function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
                $output = "";
                if ($type=="body") {
                        switch ($field_name) {
				case "Test Run":
				                $output.="<td style=\"text-align:center\"><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$value."\">".$value."</a></td> ";
                                                break;
                                case "Test Case": 
                                                $output="<td style=\"text-align:center\"><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
                                                break;
                                case "Status":
                                                $class = "";
                                                $class = "testopia_TestCase".$value;
                                                $output = "<td class=\"".$class."\">".$value."</td>";
                                                break;
				case "Bugs":	
						$output = "<td>";
						foreach (explode(",",$value) as $each_bug) {
						$output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
						}
						$output.= "</td>";
						break;
                        }
                }
                return $output;
        }
	
#	function getGoogleChartLink( &$google, $result, $type, $chart ) {
#		$field = "";
#		$color = "";
#		$colorrange = "";
#		switch ($chart) {
#			case "bstatus":
#				$field="Status";
#				$color="bs";
#				break;		
#			case "prio": 
#				$field="Priority";
#				$color="prio";
#				break;
#			case "severity":
#				$field="Severity";
#				$color="auto";
#				$colorrange="red";
#				break;				
#			default:
#				$field="Priority";
#				$color="prio";	
#		}
#	
#		if ($chart) {
#			$this->getGoogleChartOneRowCount($google, $result, $type, $chart, $field, "", $color, $colorrange);
#		} else {
#			$this->getGoogleChartOneRowCount($google,$result, $type, "prio", $field, "", $color, $colorrange);
#		}
#	}
}

?>
