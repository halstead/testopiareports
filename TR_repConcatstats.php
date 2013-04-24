<?php

class TR_repConcatstats extends TR_Template{

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
$products =  $this->getConnector()->getTable("products");
$run_ids =  explode(", ", $this->getRunID());
$run_counter = 0;
foreach ($run_ids as $run_id) {
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_plans");
		$sql->addField("test_plans", "product_id");
		$sql->addField("test_case_runs", "run_id", "run_id", "IFNULL($1,$run_id)");
		$sql->addField("test_plans", "name", "Test_Plan");
		$sql->addField("test_environments", "name", "Environment");
                $sql->addField("test_case_runs", "case_id", "status", "IFNULL(COUNT(DISTINCT $1),0)");
		$sql->addField("test_case_bugs", "bug_id", "bugs", "IFNULL(GROUP_CONCAT(DISTINCT $1),\"none\")");
		$sql->addJoin("Inner", "=", "test_runs", "plan_id", "test_plans", "plan_id");
		$sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_runs", "environment_id");
		$sql->addJoin("Inner", "=", "test_case_runs", "run_id", "test_runs", "run_id");
		$sql->addJoin("Left", "=", "test_case_bugs", "case_run_id", "test_case_runs", "case_run_id");
		$sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
		$sql->addWhere("test_case_runs", "run_id", "=", $run_id);
		$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");
		$sql->addWhere("test_case_run_status", "case_run_status_id", "=", "2", "AND");
$sqlPASSED = "select * from (".$sql->toSQL().") as temp_table GROUP BY temp_table.run_id";
#EDITED: The GROUP BY is done outside the first query because, unknown why, the result was an empty table despite the IFNULL statements.
#This resulted in an empty final result if any test run had blocked, passed or failed columns with a value of "0".

$sql = new TR_SQL;
                $sql->setConnector($this->getConnector());
                $sql->setFrom("test_plans");
		$sql->addField("test_plans", "product_id");
                $sql->addField("test_case_runs", "run_id", "run_id", "IFNULL($1,$run_id)");
                $sql->addField("test_plans", "name", "Test_Plan");
                $sql->addField("test_environments", "name", "Environment");
                $sql->addField("test_case_runs", "case_id", "status", "IFNULL(COUNT(DISTINCT $1),0)");
                $sql->addField("test_case_bugs", "bug_id", "bugs", "IFNULL(GROUP_CONCAT(DISTINCT $1),\"none\")");
                $sql->addJoin("Inner", "=", "test_runs", "plan_id", "test_plans", "plan_id");
                $sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_runs", "environment_id");
                $sql->addJoin("Inner", "=", "test_case_runs", "run_id", "test_runs", "run_id");
                $sql->addJoin("Left", "=", "test_case_bugs", "case_run_id", "test_case_runs", "case_run_id");
                $sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
                $sql->addWhere("test_case_runs", "run_id", "=", $run_id);
                $sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");
                $sql->addWhere("test_case_run_status", "case_run_status_id", "=", "3", "AND");
$sqlFAILED = "select * from (".$sql->toSQL().") as temp_table GROUP BY temp_table.run_id";

$sql = new TR_SQL;
                $sql->setConnector($this->getConnector());
                $sql->setFrom("test_plans");
		$sql->addField("test_plans", "product_id");
                $sql->addField("test_case_runs", "run_id", "run_id", "IFNULL($1,$run_id)");
                $sql->addField("test_plans", "name", "Test_Plan");
                $sql->addField("test_environments", "name", "Environment");
                $sql->addField("test_case_runs", "case_id", "status", "IFNULL(COUNT(DISTINCT $1),0)");
                $sql->addField("test_case_bugs", "bug_id", "bugs", "IFNULL(GROUP_CONCAT(DISTINCT $1),\"none\")");
                $sql->addJoin("Inner", "=", "test_runs", "plan_id", "test_plans", "plan_id");
                $sql->addJoin("Inner", "=", "test_environments", "environment_id", "test_runs", "environment_id");
                $sql->addJoin("Inner", "=", "test_case_runs", "run_id", "test_runs", "run_id");
                $sql->addJoin("Left", "=", "test_case_bugs", "case_run_id", "test_case_runs", "case_run_id");
                $sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
                $sql->addWhere("test_case_runs", "run_id", "=", $run_id);
                $sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");
                $sql->addWhere("test_case_run_status", "case_run_status_id", "=", "6", "AND");
$sqlBLOCKED ="select * from (".$sql->toSQL().") as temp_table GROUP BY temp_table.run_id";

#EDITED: The below section also treats a "NULL" Test Plan and Environment scenario. This was present in a previous implementation of this report but now it always returns the correct test plan and environment names.
	if ($run_counter == 0) {
		$result = "SELECT IFNULL(table1.product_id,IFNULL(table2.product_id,IFNULL(table3.product_id,\"NOT READY: $run_id\"))) as product_id, 
		 IFNULL(table1.run_id,IFNULL(table2.run_id,IFNULL(table3.run_id,\"NOT READY\"))) as run_id,
		 IFNULL(table1.Test_Plan,IFNULL(table2.Test_Plan,IFNULL(table3.Test_Plan,\"NOT READY\"))) AS \"Test_Plan\", 
		 IFNULL(table1.Environment,IFNULL(table2.Environment,IFNULL(table3.Environment,\"NOT READY\"))) AS \"Environment\",
		 table1.status AS Passed, table1.bugs as \"Other_issues\", 
		 table2.status AS Failed, table2.bugs as \"Failing_bugs\", 
		 table3.status AS Blocked, table3.bugs as \"Blocking_bugs\" 
		FROM ($sqlPASSED) AS table1 INNER JOIN ($sqlFAILED) as table2 ON table1.run_id=table2.run_id INNER JOIN ($sqlBLOCKED) as table3 ON table1.run_id=table3.run_id";
		$run_counter++;
	}
	else {
		$result .= " UNION ALL SELECT IFNULL(table1.product_id,IFNULL(table2.product_id,IFNULL(table3.product_id,\"NOT READY: $run_id\"))) as product_id, 
		 IFNULL(table1.run_id,IFNULL(table2.run_id,IFNULL(table3.run_id,\"NOT READY\"))) as run_id, 
		 IFNULL(table1.Test_Plan,IFNULL(table2.Test_Plan,IFNULL(table3.Test_Plan,\"NOT READY\"))) AS \"Test_Plan\", 
		 IFNULL(table1.Environment,IFNULL(table2.Environment,IFNULL(table3.Environment,\"NOT READY\"))) AS \"Environment\",
		 table1.status AS Passed, table1.bugs as \"Other_issues\", 
		 table2.status AS Failed, table2.bugs as \"Failing_bugs\", 
		 table3.status AS Blocked, table3.bugs as \"Blocking_bugs\" 
		FROM ($sqlPASSED) AS table1 INNER JOIN ($sqlFAILED) as table2 ON table1.run_id=table2.run_id INNER JOIN ($sqlBLOCKED) as table3 ON table1.run_id=table3.run_id";


	}
}

return "SELECT GROUP_CONCAT(temp_table.run_id) as \"Test Run\", temp_table.Test_Plan as \"Test Plan\", temp_table.Environment, 
        IFNULL(FORMAT(AVG(temp_table.Passed*100)/(AVG(temp_table.Passed)+AVG(temp_table.Failed)+AVG(temp_table.Blocked)),1),0) AS Passed, GROUP_CONCAT(temp_table.Other_issues) as \"Other issues\", 
        IFNULL(FORMAT(AVG(temp_table.Failed*100)/(AVG(temp_table.Passed)+AVG(temp_table.Failed)+AVG(temp_table.Blocked)),1),0) AS Failed, GROUP_CONCAT(temp_table.Failing_bugs) as \"Failing bugs\", 
        IFNULL(FORMAT(AVG(temp_table.Blocked*100)/(AVG(temp_table.Passed)+AVG(temp_table.Failed)+AVG(temp_table.Blocked)),1),0) AS Blocked, GROUP_CONCAT(temp_table.Blocking_bugs) as \"Blocking bugs\"
        FROM ($result) as temp_table INNER JOIN $products ON $products.id=temp_table.product_id GROUP BY temp_table.Test_Plan, temp_table.Environment 
        ORDER BY ($products.classification_id+0) ASC, (temp_table.product_id+0) DESC, temp_table.Environment";
	}


	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
                $output = "";
                if ($type=="body") {
                        switch ($field_name) {
				case "Test Run":
						$output = "<td align=\"left\">";
						if ($value != "none") {
                                                foreach (explode(",",$value) as $each_run) {
                                                $output.="<a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$each_run."\">".$each_run."</a> ";
                                                }
						} else {
						$output .= $value;
						}
                                                $output.= "</td>";
                                                break;

                                case "Other issues":
                                                $output = "<td>";
						$here = 0;
                                                foreach (array_unique(explode(",",$value)) as $each_bug) {
                                                if ($each_bug != "none"){
						$output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
                                                $here = 1;
						}
						} if ($here == 0) { $output .= "none"; }
                                                $output.= "</td>";
                                                break;
                                case "Failing bugs":
						$output = "<td>";
						$here = 0;
                                                foreach (array_unique(explode(",",$value)) as $each_bug) {
                                                if ($each_bug != "none"){
						$output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
                                                $here = 1;
						}
						} if ($here == 0) { $output .= "none"; }
                                                $output.= "</td>";
                                                break;
                                case "Blocking bugs":
						$output.= "<td>";
						$here = 0;
                                                foreach (array_unique(explode(",",$value)) as $each_bug) {
                                                if ($each_bug != "none"){
						$output.="<a href=\"".$this->getArgs()->get("bzserver")."/show_bug.cgi?id=".$each_bug."\">".$each_bug."</a> ";
						$here = 1;
                                                }
						} if ($here == 0) { $output .= "none"; }
                                                $output.= "</td>";
                                                break;
				case "Passed":
                                                $class = "";
						if ($value > "90.0") {
                                                $class = "testopia_TestCase"."PASSED";
                                                $output = "<td class=\"".$class."\">".$value."%</td>";
                                                } elseif ($value > "40.0") {
						$class = "testopia_TestCase"."PAUSED";
						$output = "<td class=\"".$class."\">".$value."%</td>";
						} else {
						$class = "testopia_TestCase"."FAILED";
						$output = "<td class=\"".$class."\">".$value."%</td>";
						}
						break;
				case "Failed":
						$class = "";
						if ($value > "70.0") {
						$class = "testopia_TestCase"."FAILED";
                                                $output = "<td class=\"".$class."\">".$value."%</td>";
						} elseif ($value > "30.0") { 
						$class = "testopia_TestCase"."PAUSED";
                                                $output = "<td class=\"".$class."\">".$value."%</td>";
						} else
						$output = "<td>".$value."%</td>";
						break;
				case "Blocked":
                                                $class = "";
                                                if ($value > "70.0") {
                                                $class = "testopia_TestCase"."FAILED";
                                                $output = "<td class=\"".$class."\">".$value."%</td>";
                                                } elseif ($value > "30.0") {
                                                $class = "testopia_TestCase"."PAUSED";
                                                $output = "<td class=\"".$class."\">".$value."%</td>";
                                                } else
						$output = "<td>".$value."%</td>";
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
