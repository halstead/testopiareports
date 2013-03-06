<?php
#EDITED: This file was modified to make the report function corectly.
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repConcatstats extends TR_Template{

	private $supportedCharts = array();
	
	private $supportedChartTypes = array(
		"google"   => array( "bar", "pie", "pie3" ),
	);

	function getSupportedChartsArr() {
		return $this->supportedCharts;
	}
	
	function getSupportedChartTypesArr() {
		return $this->supportedChartTypes;
	}

	/**
	* implementation of the abstract function of the super class
	*/
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
		
	function getReportName() {
		return "Testing Summary Report";
	}
		
	/**
	* Create the report header
	**/
	function getReportHeader() {
		return $this->getStandardRunHeader();
	}

	function getReportFooter() {
		#return "<b>Number of test cases in this run: ".$this->getTotal()."</b>";
		return "";
	}	
	
	function getSQL() {
$result = "";
$test_case_run_status = $this->getConnector()->getTable("test_case_run_status");
$run_ids =  explode(", ", $this->getRunID());
$run_counter = 0;
foreach ($run_ids as $run_id) {

		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_plans");
		$sql->addField("test_case_runs", "run_id");
		$sql->addField("test_plans", "name", "Test_Plan");
		$sql->addField("test_environments", "name", "Environment");
		$sql->addField("test_case_runs", "case_run_status_id", "Status");
		$sql->addField("test_case_bugs", "bug_id", "bug_id", "GROUP_CONCAT(DISTINCT $1)");
		$sql->addJoin("Inner", "=", "test_runs", "plan_id", "test_plans", "plan_id");
		$sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_runs", "environment_id");
		$sql->addJoin("Inner", "=", "test_case_runs", "run_id", "test_runs", "run_id");
		$sql->addJoin("Left", "=", "test_case_bugs", "case_run_id", "test_case_runs", "case_run_id");
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id);
		$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");
		$sql->addGroupSort("Group","test_case_runs","case_run_id");
$sql_temp = $sql->toSQL();

$sqlPASSED = "select IFNULL(temp_table.run_id,$run_id) AS run_id, temp_table.Test_Plan, temp_table.Environment, IFNULL(count(temp_table.Status),0) as PASSED, IFNULL(temp_table.bug_id,\"none\") AS bugs 
from ($sql_temp) as temp_table RIGHT JOIN $test_case_run_status ON temp_table.Status=$test_case_run_status.case_run_status_id WHERE $test_case_run_status.case_run_status_id=2 GROUP BY temp_table.run_id";

$sqlFAILED = "select IFNULL(temp_table.run_id,$run_id) AS run_id, temp_table.Test_Plan, temp_table.Environment, IFNULL(count(temp_table.Status),0) as Failed, IFNULL(temp_table.bug_id,\"none\") AS bugs 
from ($sql_temp) as temp_table RIGHT JOIN $test_case_run_status ON temp_table.Status=$test_case_run_status.case_run_status_id WHERE $test_case_run_status.case_run_status_id=3 GROUP BY temp_table.run_id";

$sqlBLOCKED = "select IFNULL(temp_table.run_id,$run_id) AS run_id, temp_table.Test_Plan, temp_table.Environment, IFNULL(count(temp_table.Status),0) as Blocked, IFNULL(temp_table.bug_id,\"none\") AS bugs
from ($sql_temp) as temp_table RIGHT JOIN $test_case_run_status ON temp_table.Status=$test_case_run_status.case_run_status_id WHERE $test_case_run_status.case_run_status_id=6 GROUP BY temp_table.run_id";

	if ($run_counter == 0) {
		$result = "SELECT IFNULL(table1.Test_Plan,IFNULL(table2.Test_Plan,IFNULL(table3.Test_Plan,\"NOT READY: $run_id\"))) AS \"Test Plan\", 
		IFNULL(table1.Environment,IFNULL(table2.Environment,IFNULL(table3.Environment,\"NOT READY: $run_id\"))) AS \"Environment\", table1.Passed, table1.bugs as \"Issues\", 
		table2.Failed, table2.bugs as \"Failing bugs\", table3.Blocked, table3.bugs as \"Blocking bugs\" 
		FROM ($sqlPASSED) AS table1 INNER JOIN ($sqlFAILED) as table2 ON table1.run_id=table2.run_id INNER JOIN ($sqlBLOCKED) as table3 ON table1.run_id=table3.run_id";
		$run_counter++;
	}
	else {
		$result .= " UNION ALL SELECT IFNULL(table1.Test_Plan,IFNULL(table2.Test_Plan,IFNULL(table3.Test_Plan,\"NOT READY: $run_id\"))) AS \"Test Plan\",
		IFNULL(table1.Environment,IFNULL(table2.Environment,IFNULL(table3.Environment,\"NOT READY: $run_id\"))) AS \"Environment\", table1.Passed, table1.bugs as \"Encountered issues\",
		table2.Failed, table2.bugs as \"Failing bugs\", table3.Blocked, table3.bugs as \"Blocking bugs\"
		FROM ($sqlPASSED) AS table1 INNER JOIN ($sqlFAILED) as table2 ON table1.run_id=table2.run_id INNER JOIN ($sqlBLOCKED) as table3 ON table1.run_id=table3.run_id";

}
}

		return $result;
	}


	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
                $output = "";
                if ($type=="body") {
                        switch ($field_name) {
                                case "Issues":
                                                $output = "<td>";
						if ($value != "none") {
                                                foreach (explode(",",$value) as $each_bug) {
                                                $output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
                                                }
						} else {
						$output .= $value;
						}
                                                $output.= "</td>";
                                                break;
                                case "Failing bugs":
                                                $output = "<td>";
						if ($value != "none") {
                                                foreach (explode(",",$value) as $each_bug) {
                                                $output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
                                                }
						} else {
						$output .= $value;
						}
                                                $output.= "</td>";
                                                break;
                                case "Blocking bugs":
                                                $output = "<td>";
						if ($value != "none") {
                                                foreach (explode(",",$value) as $each_bug) {
                                                $output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
                                                }
						} else {
						$output .= $value;
						}
                                                $output.= "</td>";

                                                break;
				case "Passed":
                                                $class = "";
                                                $class = "testopia_TestCase"."PASSED";
                                                $output = "<td class=\"".$class."\">".$value."</td>";
                                                break;
				case "Failed":
                                                $class = "";
                                                $class = "testopia_TestCase"."FAILED";
                                                $output = "<td class=\"".$class."\">".$value."</td>";
                                                break;
				case "Blocked":
                                                $class = "";
                                                $class = "testopia_TestCase"."PAUSED";
                                                $output = "<td class=\"".$class."\">".$value."</td>";
                                                break;
                        }
                }
                return $output;
        }

	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$color = new TR_Colors;
		
		$label="";
		$data="";
		$href="";
		$total=0;
		
		if (!$this->getTotal()) {
			$this->calcTotal( $result );
			$total=$this->getTotal();
		} else {
			$total = $this->getTotal();
		}
		
		$google->setDataMinRange(0);
		
		foreach ($result as $line) {
			$plotData = "";
			$i=0;
			foreach ($line as $col_value) {					
				#labels and colors
				if ($i==0) {
					$label=$col_value;
					$google->addColor($color->getColorHTML($col_value, "tcrs"));
				}
				
				#data
				if ($i==1) {
					$label.="(".$col_value.")";
					if ($total) {
						$label.= "+".round($col_value/$total*100)."%"; 
					}
					
					$google->addLabel($label);
					$google->addLegend($label);
					$google->addData($col_value);
				}
				
				$i++;
			}
		}
		
		if ($type == "bar") { $google->hideLabels( true ); }
		if ($type == "pie" or $type == "pie3") { $google->hideLegend( true ); }		
	}	
}

?>
