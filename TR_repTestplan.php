<?php
#EDITED: This file was modified to not display the Estimated Time for a test case and also to not execute the related functions.
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repTestplan extends TR_Template{

	private $supportedCharts = array( 
		"tstatus"  => array("parameter"=>"tstatus", 
							"title"    =>"Test Case Status",
							"tooltip"  =>"Test Case Status",
							"alttext"  =>"Test Case Status"),
		"testers"  => array("parameter"=>"testers", 
							"title"    =>"Tester Assignments",
							"tooltip"  =>"Tester Assignments",
							"alttext"  =>"Tester Assignments"),
		"category" => array("parameter"=>"category", 
							"title"    =>"Test Case Categories",
							"tooltip"  =>"Test Case Categories",
							"alttext"  =>"Test Case Categories"),
		"tprio"    => array("parameter"=>"tprio", 
							"title"    =>"Test Case Priorities",
							"tooltip"  =>"Test Case Priorities",
							"alttext"  =>"Test Case Priorities"),
		"creation" => array("parameter"=>"creation", 
							"title"    =>"Test Case Creation",
							"tooltip"  =>"Test Case Creation",
							"alttext"  =>"Test Case Creation"),
	
		"times"    => array("parameter"=>"times", 
							"title"    =>"Estimated Times",
							"tooltip"  =>"Estimated Times",
							"alttext"  =>"Estimated Times")
	
	);
	
	private $supportedChartTypes = array(
		"google"   => array( "pie", "pie3", "bar" ),
	);
	
	private $columnFormats = array(
		"Priority" => "text-align:center"
	); 
	
	public function getColumnFormats() {
		return $this->columnFormats;
	}	
	
	public function getSupportedChartsArr() {
		return $this->supportedCharts;
	}
	
	public function getSupportedChartTypesArr() {
		return $this->supportedChartTypes;
	}

	/**
	* implementation of the abstract function of the super class
	*/
	public function getReport( $result ) {
		# check and set some parameters
		# field for calculating the total number; could be set to a field that is included in the field list of the select statement or to rowcount, which will simply count number of rows	
		if ($this->getArgs()->get("total") == "true") {
			$this->getArgs()->set("total", "rowcount");
		}
	
		$output = $this->renderPlainHTML( $result );	
		
		#returning the output
	    return $output;
	}
		
	public function getReportName() {
		return "List of Test Cases";
	}
		
	/**
	* Create the report header
	**/
	public function getReportHeader() {
		return $this->getStandardPlanHeader();
	}

	public function getReportFooter() {
		return "";
	}	
	
	public function newLineBegin( $line ) {
		$output = "";
		if ($line["Status"] == $this->getContext()->getMessageText("trReport_Testopia_Disabled")) {
			$output = "<tr class=\"testopia_TestCaseDisabled\">";
		}
		return $output;
	}
	
	public function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		if ($type=="body") {
			switch ($field_name) {
				case "Test_Case": 
						$output="<td><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_case.cgi?case_id=".$value."\">".$value."</a></td>";
						break;
				case "Status":
						switch ($value) {
							case $this->getContext()->getMessageText("trReport_Testopia_Confirmed"): $output="<td class=\"testopia_TestCaseConfirmed\">".$value."</td>"; break;
							case $this->getContext()->getMessageText("trReport_Testopia_Proposed") : $output="<td class=\"testopia_TestCaseProposed\">".$value."</td>"; break;
							case $this->getContext()->getMessageText("trReport_Testopia_Disabled") : $output="<td class=\"testopia_TestCaseDisabled\">".$value."</td>"; break;
							default: $output = "";
						}
				default: return $output;
			}
		}
		
		return $output;
	}	
	
	function getSQL() {		
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_plans");
	if (strpos($this->getPlanID(), ",") !== FALSE) {
		$sql->addField("products", "name", "Product");
	}
		$sql->addField("test_cases", "case_id", "Test_Case");		
		$sql->addField("test_cases", "summary", "Test Case Summary");
		$sql->addField("test_case_categories", "name", "Category");
		$sql->addField("priority", "value", "Priority");
		$sql->addField("test_case_status", "name", "Status");
		$sql->addField("test_cases", "creation_date", "Creation Date", "LEFT($1,10)");
		$sql->addField("test_case_activity", "changed", "Last_Changed"); #EDITED: added a Last Changed field to the table
		$sql->addJoin("Inner", "=", "test_case_plans", "plan_id", "test_plans", "plan_id");
		$sql->addJoin("Inner", "=", "test_cases", "case_id", "test_case_plans", "case_id");
		$sql->addJoin("Left", "=", "test_case_activity", "case_id", "test_cases", "case_id"); #EDITED: added a JOIN to get the Last Changed field in the table
		$sql->addJoin("Left", "=", "test_case_categories", "category_id", "test_cases", "category_id");
		$sql->addJoin("Inner", "=", "priority", "id", "test_cases", "priority_id"); 
		$sql->addJoin("Inner", "=", "test_case_status", "case_status_id", "test_cases", "case_status_id");
	if (strpos($this->getPlanID(), ",") !== FALSE) {
		$sql->addJoin("Inner", "=", "products", "id", "test_plans", "product_id");
	}
$plan_ids =  explode(", ", $this->getPlanID());
$plan_counter = 0;
foreach ($plan_ids as $plan_id) {
	if ($plan_counter == 0) {
		$sql->addWhere("test_plans", "plan_id", "=", $plan_id);
		$sql->addWhere("test_cases", "case_status_id", "<>", "3", "AND");
		$plan_counter++;
		} else {
		$sql->addWhere("test_plans", "plan_id", "=", $plan_id, "OR");
		$sql->addWhere("test_cases", "case_status_id", "<>", "3", "AND");
		}
}
		$result=$sql->toSQL();
return "select * from (select * from (select * from ($result) as test order by Last_Changed DESC) as test2 group by Test_Case) as test3 order by Product ASC, Test_Case";
	}	
		
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$field = "";
		$color = "";
		$colorrange = "";
		
		switch ($chart) {
			case "Category":
					$field="Category";
					$colorrange = "orange";
					$color="auto";
					break;
			case "tstatus" :
					$field="Status";
					$color="tstatus";			
					break;
			case "tprio" :
					$field="Priority";
					$color="prio";
					break;	
			case "creation":
					$field="Creation Date";
					$color="auto"; 
					$colorrange="rainbow";
					break;											
			default:
					$field="Tester";
					$color="auto"; 
					$colorrange = "rainbow";			
		}
	
		if ($chart) {
			$this->getGoogleChartOneRowCount($google, $result, $type, $chart, $field, "", $color, $colorrange);
		} else {
			$this->getGoogleChartOneRowCount($google,$result, $type, "testers", $field, "", $color, $colorrange);
		}
	}	
}

?>