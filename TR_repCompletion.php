<?php
#EDITED: To track the changes made besides the commented-out sections, follow the tag "EDITED"

class TR_repCompletion extends TR_Template{
	
	var $supportedCharts = array(
		"std"   => array("parameter"=>"std", 
						 "title"    =>"Test Run Completion",
						 "tooltip"  =>"Test Run Completion",
						 "alttext"  =>"Test Run Completion"),
		"meter" => array("parameter"=>"meter", 
						 "title"    =>"Test Run Completion",
						 "tooltip"  =>"Test Run Completion (meter)",
						 "alttext"  =>"Test Run Completion (meter)")
	);
	
	var $supportedChartTypes = array(
		"google"   => array( "bar", "pie", "pie3"),
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
		$run_id = $this->getArgs()->get("run_id");
		
		$sql = "SELECT count(distinct bug_id) as Bugs FROM ".$this->getConnector()->getTable("test_case_bugs");
		$sql.= " INNER JOIN ".$this->getConnector()->getTable("test_case_runs")." ON test_case_bugs.case_run_id = test_case_runs.case_run_id WHERE run_id = ".$run_id.";";
		$result = $this->getConnector()->execute($sql);
		
		if ($result) {
			$line = $this->getConnector()->fetch($result);
			$bugs = $line["Bugs"];
			$this->getConnector()->free($result);
		} else {
			$bugs = "0";
		}

		return "<b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$run_id."\">Bugs found in this run: ".$bugs."</a></b>";
	}
	
	function getReportName() {
		return "Test Run Completition Report";
	}		
	
	function getReport( $result ) {
		# check and set some parameters		
		$this->getArgs()->set("total","");
		$this->getArgs()->set("sortable","false");
		
		$output = $this->renderPlainHTML( $result );
		
		#returning the output
	    return $output;
	}
	
	function getSQL() {		
		$sql = new TR_SQL;
		$sql->setConnector($this->getConnector());
		$sql->setFrom("test_case_runs");
		$sql->addField("test_case_runs", "case_run_status_id", "Completed", "sum(case when $1 IN (2,3,6) then 1 else 0 end)");
		$sql->addField("test_case_runs", "case_run_status_id", "Not Complete", "sum(case when $1 IN (1,4,5,7) then 1 else 0 end)");
		$sql->addField("test_case_runs", "case_run_status_id", "Passed", "sum(case when $1 IN (2) then 1 else 0 end)");
		$sql->addField("test_case_runs", "case_run_status_id", "Failed", "sum(case when $1 IN (3) then 1 else 0 end)");		
		$sql->addField("test_case_runs", "case_run_status_id", "Blocked", "sum(case when $1 IN (6) then 1 else 0 end)");			
		$sql->addField("test_case_runs", "case_run_status_id", "Total", "sum(case when $1 IN (1,2,3,4,5,6,7) then 1 else 0 end)");	
		$sql->addWhere("test_case_runs", "run_id", "=", $this->getRunID());
$sql->addWhere("test_case_runs", "iscurrent", "=", "1", "AND"); #Edited: added this to fetch only the current environment/build of a case run within the current test run
		return $sql->toSQL();
	}
	
	function renderCell($type, $colNo, $field_name, $value, $lineNo, $line) {
		$output = "";
		
		if ($type=="header") {
			switch ($value) {
				case "Completed": 
						$output="<th colspan=\"2\"><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."&case_run_status=PASSED&case_run_status=FAILED&case_run_status=BLOCKED\">".$value."</a></b></th>";
						break;
				case "Not Complete": 
						$output="<th colspan=\"2\"><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."&case_run_status=IDLE&case_run_status=PAUSED&case_run_status=RUNNING&case_run_status=ERROR\">".$value."</a></b></th>";
						break;
				case "Passed": 
						$output="<th colspan=\"2\"><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."&case_run_status=PASSED\">".$value."</a></b></th>";
						break;
				case "Failed": 
						$output="<th colspan=\"2\"><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."&case_run_status=FAILED\">".$value."</a></b></th>";				
						break;
				case "Blocked": 
						$output="<th colspan=\"2\"><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."&case_run_status=BLOCKED\">".$value."</a></b></th>";				
						break;
				case "Total": 
						$output="<th><b><a href=\"".$this->getArgs()->get("bzserver")."/tr_show_run.cgi?run_id=".$this->getRunID()."\">".$value."</a></b></th>";
						break;
			}			
		}
		
		if ($type == "body") {
			$total=$line["Total"];			
			$perc = $this->getPerc($value,$total);
			
			if ($field_name != "Total") {
				$output="<td>".$value."</td><td>"."(".$perc.")"."</td>";
			} 
		}
		
		return $output;
	}
	
	function getGoogleChartLink( &$google, $result, $type, $chart ) {
		$color = new TR_Colors;
		
		if ($chart == "meter") {
			$google->setType("gom");
			$type="meter";
		} elseif ($chart == "") {
			$chart="std";
		}
		
		$label="";
		$data="";
		$href="";
		$total=0;
		
		$google->setDataMinRange(0);
		$google->setDimCoef(3.3);
		$google->setDimCoef3D(3.8);
		
		foreach ($result as $line) {
			$i=0;
			$total=$line["Total"];
			foreach ($line as $col_value) {	
				$name = $this->columnNames[$i];
				if ($col_value > 0 and $name!="Total") {				
					$perc = $this->getPerc($col_value,$total,false);
					
					if ($name == "Completed" and $type =="meter") {
						$google->addData($perc);
						$google->addLabel("Completed (".$perc."%)");
					} 
					
					$google->addColor($color->getColorHTML($name, "compl"));
					if ($type != "meter") {
						$google->addData($col_value);
						$label=$name."(".$col_value."%) ".$perc;
						$google->addLabel($label);
						$google->addLegend($label);					
					}
				} elseif ($col_value == 0 and $name == "Completed" and $type =="meter") {
						$google->addData(0);
						$google->setDimCoefMeter(3.1);
						$google->addLabel("Completed (0%)");
				}
				
				$i++;
			}
		}
		
		if ($type == "bar") { $google->hideLabels( true ); }
		if ($type == "pie" or $type == "pie3" or $type == "meter") { $google->hideLegend( true ); }	
	}	
}

?>