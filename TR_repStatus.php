<?php
#EDITED: This file was modified to make the report function corectly.
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repStatus extends TR_Template{

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
			$this->getArgs()->set("total", "Count");
		}
	
		$output = $this->renderPlainHTML( $result );	
		
		#returning the output
	    return $output;
	}
		
	function getReportName() {
		return "Test Case Status Report";
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
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_case_runs");
		$sql->addField("test_case_run_status", "name", "Status");
		$sql->addField("test_case_runs", "case_run_id", "Count", "count(distinct $1)");
#		$sql->addJoin("Inner", "=", "test_case_runs", "case_id", "test_cases", "case_id");
		$sql->addJoin("Inner", "=", "test_case_run_status", "case_run_status_id", "test_case_runs", "case_run_status_id");
$run_ids =  explode(", ", $this->getRunID());
$run_counter = 0;
foreach ($run_ids as $run_id) {
		if ($run_counter == 0) {
			$sql->addWhere("test_case_runs", "run_id", "=", $run_id);
			$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");#EDITED: added this line to use only current build/environment for ca case run
			$run_counter ++;
		}
		else {
			$sql->addWhere("test_case_runs", "run_id", "=", $run_id, "OR");
			$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND");#EDITED: added this line to use only current build/environment for ca case run

		}
}
#		$sql->addGroupSort("Group","test_case_runs", "run_id");
		$sql->addGroupSort("Group","test_case_run_status", "name");
#		$sql->addGroupSort("Order","test_case_run_status", "name");
				
#var_dump($sql->toSQL());
#exit;
		return $sql->toSQL();


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